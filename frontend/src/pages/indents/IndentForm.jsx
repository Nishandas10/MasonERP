import React, { useEffect, useState } from 'react';
import { useForm, useFieldArray } from 'react-hook-form';
import { useNavigate, useParams } from 'react-router-dom';
import { indentApi, projectApi, materialApi } from '../../api/endpoints';
import PageHeader from '../../components/common/PageHeader';
import { toast } from 'react-toastify';

export default function IndentForm() {
  const { id } = useParams();
  const isEdit = Boolean(id);
  const navigate = useNavigate();
  const [projects, setProjects] = useState([]);
  const [materials, setMaterials] = useState([]);

  const { register, handleSubmit, control, reset, formState: { errors, isSubmitting } } = useForm({
    defaultValues: {
      project_id: '',
      indent_date: '',
      required_by_date: '',
      remarks: '',
      items: [{ material_id: '', quantity: '', unit: '', specifications: '' }],
    },
  });

  const { fields, append, remove } = useFieldArray({ control, name: 'items' });

  useEffect(() => {
    projectApi.list({ per_page: 100 }).then((res) => setProjects(res.data.data?.data || []));
    materialApi.list({ per_page: 100 }).then((res) => setMaterials(res.data.data?.data || []));

    if (isEdit) {
      indentApi.get(id).then((res) => {
        const indent = res.data.data;
        reset({
          project_id: indent.project_id,
          indent_date: indent.indent_date,
          required_by_date: indent.required_by_date,
          remarks: indent.remarks,
          items: indent.items?.map((i) => ({
            material_id: i.material_id,
            quantity: i.quantity,
            unit: i.unit,
            specifications: i.specifications,
          })) || [{ material_id: '', quantity: '', unit: '', specifications: '' }],
        });
      });
    }
  }, [id, isEdit, reset]);

  const onSubmit = async (data) => {
    try {
      if (isEdit) {
        await indentApi.update(id, data);
        toast.success('Indent updated.');
      } else {
        await indentApi.create(data);
        toast.success('Indent created.');
      }
      navigate('/indents');
    } catch (err) {
      const errs = err.response?.data?.errors;
      const msg = err.response?.data?.message;
      if (errs) Object.values(errs).flat().forEach((m) => toast.error(m));
      else if (msg) toast.error(msg);
    }
  };

  return (
    <div>
      <PageHeader title={isEdit ? 'Edit Indent' : 'New Material Indent'} />
      <div className="card border-0 shadow-sm">
        <div className="card-body p-4">
          <form onSubmit={handleSubmit(onSubmit)}>
            <div className="row g-3 mb-4">
              <div className="col-md-6">
                <label className="form-label fw-semibold">Project *</label>
                <select
                  className={`form-select ${errors.project_id ? 'is-invalid' : ''}`}
                  {...register('project_id', { required: 'Project is required' })}
                >
                  <option value="">Select project...</option>
                  {projects.map((p) => (
                    <option key={p.id} value={p.id}>{p.name}</option>
                  ))}
                </select>
                {errors.project_id && <div className="invalid-feedback">{errors.project_id.message}</div>}
              </div>
              <div className="col-md-3">
                <label className="form-label fw-semibold">Indent Date *</label>
                <input
                  type="date"
                  className={`form-control ${errors.indent_date ? 'is-invalid' : ''}`}
                  {...register('indent_date', { required: 'Indent date is required' })}
                />
                {errors.indent_date && <div className="invalid-feedback">{errors.indent_date.message}</div>}
              </div>
              <div className="col-md-3">
                <label className="form-label fw-semibold">Required By</label>
                <input type="date" className="form-control" {...register('required_by_date')} />
              </div>
              <div className="col-12">
                <label className="form-label fw-semibold">Remarks</label>
                <input className="form-control" {...register('remarks')} />
              </div>
            </div>

            <div className="d-flex justify-content-between align-items-center mb-2">
              <h6 className="fw-bold mb-0">Items</h6>
              <button
                type="button"
                className="btn btn-sm btn-outline-primary"
                onClick={() => append({ material_id: '', quantity: '', unit: '', specifications: '' })}
              >
                + Add Item
              </button>
            </div>

            <div className="table-responsive mb-3">
              <table className="table table-bordered align-middle">
                <thead className="table-light">
                  <tr>
                    <th>Material *</th>
                    <th style={{ width: 120 }}>Qty *</th>
                    <th style={{ width: 120 }}>Unit *</th>
                    <th>Specifications</th>
                    <th style={{ width: 50 }}></th>
                  </tr>
                </thead>
                <tbody>
                  {fields.map((field, index) => (
                    <tr key={field.id}>
                      <td>
                        <select
                          className="form-select form-select-sm"
                          {...register(`items.${index}.material_id`, { required: true })}
                        >
                          <option value="">Select material...</option>
                          {materials.map((m) => (
                            <option key={m.id} value={m.id}>{m.name}</option>
                          ))}
                        </select>
                      </td>
                      <td>
                        <input
                          type="number"
                          min="0.01"
                          step="0.01"
                          className="form-control form-control-sm"
                          {...register(`items.${index}.quantity`, { required: true, min: 0.01 })}
                        />
                      </td>
                      <td>
                        <input
                          className="form-control form-control-sm"
                          {...register(`items.${index}.unit`)}
                        />
                      </td>
                      <td>
                        <input
                          className="form-control form-control-sm"
                          {...register(`items.${index}.specifications`)}
                        />
                      </td>
                      <td className="text-center">
                        {fields.length > 1 && (
                          <button
                            type="button"
                            className="btn btn-sm btn-outline-danger"
                            onClick={() => remove(index)}
                          >×</button>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            <div className="d-flex gap-2">
              <button type="submit" className="btn btn-primary" disabled={isSubmitting}>
                {isSubmitting ? <><span className="spinner-border spinner-border-sm me-2" />Saving...</> : 'Save Indent'}
              </button>
              <button type="button" className="btn btn-outline-secondary" onClick={() => navigate('/indents')}>
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}
