import React, { useEffect, useState, useCallback } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { vendorApi } from '../../api/endpoints';
import PageHeader from '../../components/common/PageHeader';
import StatusBadge from '../../components/common/StatusBadge';
import { toast } from 'react-toastify';

const fmt = (n) => Number(n || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const STATUS_COLORS = {
  draft: 'secondary',
  sent: 'info',
  acknowledged: 'primary',
  partially_received: 'warning',
  received: 'success',
  cancelled: 'danger',
};

export default function VendorDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [vendor, setVendor] = useState(null);
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);

  const load = useCallback(() => {
    setLoading(true);
    vendorApi.get(id)
      .then((res) => {
        setVendor(res.data.data);
        setStats(res.data.stats);
      })
      .catch(() => toast.error('Failed to load vendor.'))
      .finally(() => setLoading(false));
  }, [id]);

  useEffect(() => { load(); }, [load]);

  if (loading) return <div className="text-center py-5"><div className="spinner-border text-primary" /></div>;
  if (!vendor) return <div className="alert alert-danger">Vendor not found.</div>;

  const orders = vendor.purchase_orders || [];

  // Aggregate materials bought across all POs
  const materialMap = {};
  orders.forEach((po) => {
    (po.items || []).forEach((item) => {
      const key = item.material_id;
      if (!materialMap[key]) {
        materialMap[key] = {
          name: item.material?.name || `Material #${key}`,
          unit: item.unit,
          totalQty: 0,
          receivedQty: 0,
          totalAmount: 0,
        };
      }
      materialMap[key].totalQty += Number(item.quantity);
      materialMap[key].receivedQty += Number(item.received_quantity || 0);
      materialMap[key].totalAmount += Number(item.quantity) * Number(item.rate);
    });
  });
  const materials = Object.values(materialMap);

  return (
    <div>
      <PageHeader
        title={vendor.name}
        action={
          <div className="d-flex gap-2">
            <Link to="/vendors" className="btn btn-outline-secondary">← Back</Link>
            <button className="btn btn-outline-primary" onClick={() => navigate(`/vendors/${id}/edit`)}>Edit</button>
          </div>
        }
      />

      {/* Stats Row */}
      <div className="row g-3 mb-4">
        {[
          { label: 'Total Orders', value: stats?.total_orders ?? 0, icon: 'bi-cart3', color: 'primary' },
          { label: 'Total Ordered', value: `₹${fmt(stats?.total_ordered)}`, icon: 'bi-receipt', color: 'info' },
          { label: 'Total Received', value: `₹${fmt(stats?.total_received)}`, icon: 'bi-box-seam', color: 'success' },
          { label: 'Pending Orders', value: stats?.pending_orders ?? 0, icon: 'bi-hourglass-split', color: 'warning' },
          { label: 'Amount Due', value: `₹${fmt(stats?.amount_due)}`, icon: 'bi-exclamation-circle', color: 'danger' },
        ].map((s) => (
          <div key={s.label} className="col-6 col-md-4 col-lg">
            <div className="card border-0 shadow-sm h-100">
              <div className="card-body">
                <div className="d-flex align-items-center gap-2 mb-1">
                  <i className={`bi ${s.icon} text-${s.color}`} />
                  <small className="text-muted">{s.label}</small>
                </div>
                <div className="fs-5 fw-bold">{s.value}</div>
              </div>
            </div>
          </div>
        ))}
      </div>

      <div className="row g-3">
        {/* Vendor Info */}
        <div className="col-lg-4">
          <div className="card border-0 shadow-sm mb-3">
            <div className="card-header bg-white fw-semibold">Vendor Info</div>
            <div className="card-body">
              <table className="table table-sm table-borderless mb-0">
                <tbody>
                  {[
                    ['Code', vendor.code],
                    ['Contact Person', vendor.contact_person],
                    ['Phone', vendor.phone],
                    ['Email', vendor.email],
                    ['City', vendor.city],
                    ['State', vendor.state],
                    ['GSTIN', vendor.gstin],
                    ['PAN', vendor.pan],
                    ['Status', <StatusBadge status={vendor.status} />],
                  ].map(([label, val]) => val ? (
                    <tr key={label}>
                      <td className="text-muted fw-semibold" style={{ width: '45%' }}>{label}</td>
                      <td>{val}</td>
                    </tr>
                  ) : null)}
                </tbody>
              </table>
            </div>
          </div>
          {(vendor.bank_name || vendor.bank_account) && (
            <div className="card border-0 shadow-sm">
              <div className="card-header bg-white fw-semibold">Bank Details</div>
              <div className="card-body">
                <table className="table table-sm table-borderless mb-0">
                  <tbody>
                    {[
                      ['Bank', vendor.bank_name],
                      ['Account', vendor.bank_account],
                      ['IFSC', vendor.bank_ifsc],
                    ].map(([label, val]) => val ? (
                      <tr key={label}>
                        <td className="text-muted fw-semibold" style={{ width: '45%' }}>{label}</td>
                        <td>{val}</td>
                      </tr>
                    ) : null)}
                  </tbody>
                </table>
              </div>
            </div>
          )}
        </div>

        {/* Right Column */}
        <div className="col-lg-8">
          {/* Purchase Orders */}
          <div className="card border-0 shadow-sm mb-3">
            <div className="card-header bg-white d-flex justify-content-between align-items-center">
              <span className="fw-semibold">Purchase Orders</span>
              <Link className="btn btn-sm btn-primary" to={`/purchase-orders/new?vendor_id=${id}`}>
                + New PO
              </Link>
            </div>
            <div className="card-body p-0">
              {orders.length === 0 ? (
                <p className="text-muted p-3 mb-0">No purchase orders yet.</p>
              ) : (
                <div className="table-responsive">
                  <table className="table table-sm table-hover align-middle mb-0">
                    <thead className="table-light">
                      <tr>
                        <th>PO #</th>
                        <th>Project</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      {orders.map((po) => (
                        <tr key={po.id}>
                          <td className="fw-semibold">{po.po_number}</td>
                          <td>{po.project?.name || '—'}</td>
                          <td>{String(po.po_date || '').slice(0, 10)}</td>
                          <td>₹{fmt(po.total_amount)}</td>
                          <td>
                            <span className={`badge bg-${STATUS_COLORS[po.status] || 'secondary'}`}>
                              {po.status?.replace(/_/g, ' ')}
                            </span>
                          </td>
                          <td>
                            <Link className="btn btn-sm btn-outline-secondary" to={`/purchase-orders/${po.id}/edit`}>
                              Edit
                            </Link>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                    <tfoot className="table-light">
                      <tr>
                        <td colSpan={3} className="fw-bold text-end">Total</td>
                        <td className="fw-bold">₹{fmt(stats?.total_ordered)}</td>
                        <td colSpan={2}></td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              )}
            </div>
          </div>

          {/* Materials Bought */}
          <div className="card border-0 shadow-sm">
            <div className="card-header bg-white fw-semibold">Materials Purchased</div>
            <div className="card-body p-0">
              {materials.length === 0 ? (
                <p className="text-muted p-3 mb-0">No materials purchased yet.</p>
              ) : (
                <div className="table-responsive">
                  <table className="table table-sm table-hover align-middle mb-0">
                    <thead className="table-light">
                      <tr>
                        <th>Material</th>
                        <th>Unit</th>
                        <th>Ordered Qty</th>
                        <th>Received Qty</th>
                        <th>Amount</th>
                      </tr>
                    </thead>
                    <tbody>
                      {materials.map((m, i) => (
                        <tr key={i}>
                          <td className="fw-semibold">{m.name}</td>
                          <td>{m.unit}</td>
                          <td>{Number(m.totalQty).toLocaleString('en-IN')}</td>
                          <td>
                            <span className={m.receivedQty < m.totalQty ? 'text-warning fw-semibold' : 'text-success fw-semibold'}>
                              {Number(m.receivedQty).toLocaleString('en-IN')}
                            </span>
                          </td>
                          <td>₹{fmt(m.totalAmount)}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
