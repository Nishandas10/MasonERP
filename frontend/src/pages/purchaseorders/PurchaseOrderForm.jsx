import React, { useEffect, useState } from 'react';
import { useForm, useFieldArray } from 'react-hook-form';
import { useNavigate, useParams, useSearchParams } from 'react-router-dom';
import { purchaseOrderApi, vendorApi, projectApi, materialApi, indentApi } from '../../api/endpoints';
import PageHeader from '../../components/common/PageHeader';
import { toast } from 'react-toastify';

const PO_STATUSES = ['draft', 'sent', 'acknowledged', 'partially_received', 'received', 'cancelled'];

export default function PurchaseOrderForm() {
  const { id } = useParams();
  const [searchParams] = useSearchParams();
  const isEdit = Boolean(id);
  const navigate = useNavigate();

  const [vendors, setVendors] = useState([]);
  const [projects, setProjects] = useState([]);
  const [materials, setMaterials] = useState([]);
  const [linkedIndent, setLinkedIndent] = useState(null);

  const indentId = searchParams.get('indent_id');

  const { register, handleSubmit, control, reset, watch, formState: { errors, isSubmitting } } = useForm({
    defaultValues: {
      vendor_id: searchParams.get('vendor_id') || '',
      project_id: searchParams.get('project_id') || '',
      indent_id: indentId || '',
      po_date: new Date().toISOString().slice(0, 10),
      delivery_date: '',
      delivery_address: '',
      status: 'draft',
      terms_and_conditions: '',
      items: [{ material_id: '', quantity: '', unit: '', rate: '', tax_percent: '0', received_quantity: '0' }],
    },
  });

  const { fields, append, remove } = useFieldArray({ control, name: 'items' });
  const watchItems = watch('items');

  useEffect(() => {
    Promise.all([
      vendorApi.list({ per_page: 100 }),
      projectApi.list({ per_page: 100 }),
      materialApi.list({ per_page: 100 }),
    ]).then(([vRes, pRes, mRes]) => {
      const mats = mRes.data.data?.data || [];
      setVendors(vRes.data.data?.data || []);
      setProjects(pRes.data.data?.data || []);
      setMaterials(mats);

      // Pre-fill from indent when coming from indent detail
      if (!isEdit && indentId) {
        indentApi.get(indentId).then((iRes) => {
          const indent = iRes.data.data;
          setLinkedIndent(indent);
          reset((prev) => ({
            ...prev,
            project_id: indent.project_id || prev.project_id,
            indent_id: indent.id,
            items: (indent.items || []).map((item) => {
              const mat = mats.find((m) => m.id === item.material_id);
              return {
                material_id: item.material_id,
                quantity: item.quantity,
                unit: item.unit || mat?.unit || '',
                rate: mat?.standard_rate || '',
                tax_percent: '0',
                received_quantity: '0',
              };
            }),
          }));
        }).catch(() => toast.error('Failed to load indent.'));
      }
    });

    if (isEdit) {
      purchaseOrderApi.get(id).then((res) => {
        const po = res.data.data;
        reset({
          vendor_id: po.vendor_id,
          project_id: po.project_id,
          po_date: po.po_date?.slice(0, 10) || '',
          delivery_date: po.delivery_date?.slice(0, 10) || '',
          delivery_address: po.delivery_address || '',
          status: po.status || 'draft',
          terms_and_conditions: po.terms_and_conditions || '',
          items: po.items?.map((i) => ({
            material_id: i.material_id,
            quantity: i.quantity,
            unit: i.unit,
            rate: i.rate,
            tax_percent: i.tax_percent ?? 0,
            received_quantity: i.received_quantity ?? 0,
          })) || [{ material_id: '', quantity: '', unit: '', rate: '', tax_percent: '0', received_quantity: '0' }],
        });
      }).catch(() => toast.error('Failed to load purchase order.'));
    }
  }, [id, isEdit, reset]);

  // Auto-fill unit from selected material
  const handleMaterialChange = (index, materialId) => {
    const mat = materials.find((m) => String(m.id) === String(materialId));
    if (mat) {
      const current = watchItems[index] || {};
      reset((prev) => {
        const items = [...prev.items];
        items[index] = { ...items[index], unit: mat.unit, rate: mat.standard_rate || items[index].rate };
        return { ...prev, items };
      });
    }
  };

  // Calculate totals
  const subtotal = watchItems.reduce((sum, item) => {
    const amt = Number(item.quantity || 0) * Number(item.rate || 0);
    return sum + amt;
  }, 0);
  const taxTotal = watchItems.reduce((sum, item) => {
    const amt = Number(item.quantity || 0) * Number(item.rate || 0);
    return sum + amt * (Number(item.tax_percent || 0) / 100);
  }, 0);
  const grandTotal = subtotal + taxTotal;

  const fmt = (n) => Number(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  const onSubmit = async (data) => {
    try {
      const vendorId = data.vendor_id;
      let savedId = id;
      if (isEdit) {
        await purchaseOrderApi.update(id, data);
        toast.success('Purchase order updated.');
      } else {
        const res = await purchaseOrderApi.create(data);
        savedId = res.data.data?.id;
        toast.success('Purchase order created.');
      }
      // Navigate: back to indent if came from there, else vendor, else list
      if (!isEdit && indentId) {
        navigate(`/indents/${indentId}`);
      } else if (vendorId && !isEdit) {
        navigate(`/vendors/${vendorId}`);
      } else {
        navigate('/purchase-orders');
      }
    } catch (err) {
      const errs = err.response?.data?.errors;
      if (errs) Object.values(errs).flat().forEach((m) => toast.error(m));
      else toast.error(err.response?.data?.message || 'Failed to save.');
    }
  };

  return (
    <div>
      <PageHeader
        title={isEdit ? 'Edit Purchase Order' : 'New Purchase Order'}
        action={
          <button type="button" className="btn btn-outline-secondary"
            onClick={() => navigate(isEdit ? `/purchase-orders/${id}` : '/purchase-orders')}>
            Cancel
          </button>
        }
      />
      <div className="card border-0 shadow-sm">
        <div className="card-body p-4">
          <form onSubmit={handleSubmit(onSubmit)}>
            {/* Hidden indent_id */}
            <input type="hidden" {...register('indent_id')} />

            {/* Linked indent banner */}
            {linkedIndent && (
              <div className="alert alert-info d-flex align-items-center gap-2 mb-3">
                <i className="bi bi-clipboard-check-fill" />
                <div>
                  <strong>From Indent:</strong> {linkedIndent.indent_number}
                  {linkedIndent.remarks && <span className="ms-2 text-muted">— {linkedIndent.remarks}</span>}
                  <span className="ms-2 badge bg-info text-dark">
                    Required by {String(linkedIndent.required_by_date || '').slice(0, 10) || '—'}
                  </span>
                </div>
              </div>
            )}
            <div className="row g-3 mb-4">
              <div className="col-md-4">
                <label className="form-label fw-semibold">Vendor *</label>
                <select className={`form-select ${errors.vendor_id ? 'is-invalid' : ''}`}
                  {...register('vendor_id', { required: 'Vendor is required' })}>
                  <option value="">Select vendor...</option>
                  {vendors.map((v) => <option key={v.id} value={v.id}>{v.name}</option>)}
                </select>
                {errors.vendor_id && <div className="invalid-feedback">{errors.vendor_id.message}</div>}
              </div>
              <div className="col-md-4">
                <label className="form-label fw-semibold">Project *</label>
                <select className={`form-select ${errors.project_id ? 'is-invalid' : ''}`}
                  {...register('project_id', { required: 'Project is required' })}>
                  <option value="">Select project...</option>
                  {projects.map((p) => <option key={p.id} value={p.id}>{p.name}</option>)}
                </select>
                {errors.project_id && <div className="invalid-feedback">{errors.project_id.message}</div>}
              </div>
              <div className="col-md-4">
                <label className="form-label fw-semibold">Status</label>
                <select className="form-select" {...register('status')}>
                  {PO_STATUSES.map((s) => <option key={s} value={s}>{s.replace(/_/g, ' ')}</option>)}
                </select>
              </div>
              <div className="col-md-3">
                <label className="form-label fw-semibold">PO Date *</label>
                <input type="date" className={`form-control ${errors.po_date ? 'is-invalid' : ''}`}
                  {...register('po_date', { required: 'PO date is required' })} />
                {errors.po_date && <div className="invalid-feedback">{errors.po_date.message}</div>}
              </div>
              <div className="col-md-3">
                <label className="form-label fw-semibold">Delivery Date</label>
                <input type="date" className="form-control" {...register('delivery_date')} />
              </div>
              <div className="col-md-6">
                <label className="form-label fw-semibold">Delivery Address</label>
                <input className="form-control" {...register('delivery_address')} />
              </div>
            </div>

            {/* Items Table */}
            <div className="d-flex justify-content-between align-items-center mb-2">
              <h6 className="fw-bold mb-0">Line Items</h6>
              <button type="button" className="btn btn-sm btn-outline-primary"
                onClick={() => append({ material_id: '', quantity: '', unit: '', rate: '', tax_percent: '0', received_quantity: '0' })}>
                + Add Item
              </button>
            </div>

            <div className="table-responsive mb-3">
              <table className="table table-bordered align-middle" style={{ minWidth: 900 }}>
                <thead className="table-light">
                  <tr>
                    <th style={{ minWidth: 200 }}>Material *</th>
                    <th style={{ width: 90 }}>Qty *</th>
                    <th style={{ width: 80 }}>Unit *</th>
                    <th style={{ width: 110 }}>Rate (₹) *</th>
                    <th style={{ width: 90 }}>Tax %</th>
                    <th style={{ width: 110 }}>Received Qty</th>
                    <th style={{ width: 120 }}>Amount</th>
                    <th style={{ width: 50 }}></th>
                  </tr>
                </thead>
                <tbody>
                  {fields.map((field, index) => {
                    const qty = Number(watchItems[index]?.quantity || 0);
                    const rate = Number(watchItems[index]?.rate || 0);
                    const tax = Number(watchItems[index]?.tax_percent || 0);
                    const lineAmt = qty * rate;
                    const lineTotal = lineAmt + lineAmt * (tax / 100);
                    return (
                      <tr key={field.id}>
                        <td>
                          <select className="form-select form-select-sm"
                            {...register(`items.${index}.material_id`, { required: true })}
                            onChange={(e) => {
                              register(`items.${index}.material_id`).onChange(e);
                              handleMaterialChange(index, e.target.value);
                            }}>
                            <option value="">Select...</option>
                            {materials.map((m) => <option key={m.id} value={m.id}>{m.name}</option>)}
                          </select>
                        </td>
                        <td>
                          <input type="number" min="0.001" step="0.001" className="form-control form-control-sm"
                            {...register(`items.${index}.quantity`, { required: true, min: 0.001 })} />
                        </td>
                        <td>
                          <input className="form-control form-control-sm"
                            {...register(`items.${index}.unit`, { required: true })} />
                        </td>
                        <td>
                          <input type="number" min="0" step="0.01" className="form-control form-control-sm"
                            {...register(`items.${index}.rate`, { required: true, min: 0 })} />
                        </td>
                        <td>
                          <input type="number" min="0" max="100" step="0.01" className="form-control form-control-sm"
                            {...register(`items.${index}.tax_percent`)} />
                        </td>
                        <td>
                          <input type="number" min="0" step="0.001" className="form-control form-control-sm"
                            {...register(`items.${index}.received_quantity`)} />
                        </td>
                        <td className="text-end fw-semibold">₹{fmt(lineTotal)}</td>
                        <td className="text-center">
                          {fields.length > 1 && (
                            <button type="button" className="btn btn-sm btn-outline-danger" onClick={() => remove(index)}>×</button>
                          )}
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
                <tfoot className="table-light">
                  <tr>
                    <td colSpan={6} className="text-end fw-semibold">Subtotal</td>
                    <td className="text-end fw-semibold">₹{fmt(subtotal)}</td>
                    <td></td>
                  </tr>
                  <tr>
                    <td colSpan={6} className="text-end fw-semibold">Tax</td>
                    <td className="text-end fw-semibold">₹{fmt(taxTotal)}</td>
                    <td></td>
                  </tr>
                  <tr className="table-primary">
                    <td colSpan={6} className="text-end fw-bold">Grand Total</td>
                    <td className="text-end fw-bold">₹{fmt(grandTotal)}</td>
                    <td></td>
                  </tr>
                </tfoot>
              </table>
            </div>

            <div className="col-12 mb-4">
              <label className="form-label fw-semibold">Terms & Conditions</label>
              <textarea rows={3} className="form-control" {...register('terms_and_conditions')} />
            </div>

            <div className="d-flex gap-2">
              <button type="submit" className="btn btn-primary" disabled={isSubmitting}>
                {isSubmitting
                  ? <><span className="spinner-border spinner-border-sm me-2" />Saving...</>
                  : isEdit ? 'Update PO' : 'Create PO'}
              </button>
              <button type="button" className="btn btn-outline-secondary"
                onClick={() => navigate(isEdit ? `/purchase-orders/${id}` : '/purchase-orders')}>
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}
