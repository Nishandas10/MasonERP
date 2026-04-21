import React, { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { purchaseOrderApi } from '../../api/endpoints';
import PageHeader from '../../components/common/PageHeader';
import { toast } from 'react-toastify';

const STATUS_BADGE = {
  draft: 'secondary',
  sent: 'info',
  acknowledged: 'primary',
  partially_received: 'warning',
  received: 'success',
  cancelled: 'danger',
};

const fmt = (n) => Number(n || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });

export default function PurchaseOrderList() {
  const navigate = useNavigate();
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    purchaseOrderApi.list({ per_page: 50 })
      .then((res) => setOrders(res.data.data?.data || []))
      .catch(() => toast.error('Failed to load purchase orders.'))
      .finally(() => setLoading(false));
  }, []);

  return (
    <div>
      <PageHeader
        title="Purchase Orders"
        action={
          <button className="btn btn-primary" onClick={() => navigate('/purchase-orders/new')}>
            + New PO
          </button>
        }
      />
      <div className="card border-0 shadow-sm">
        <div className="card-body p-0">
          {loading ? (
            <div className="text-center py-5"><span className="spinner-border text-primary" /></div>
          ) : orders.length === 0 ? (
            <div className="text-center text-muted py-5">No purchase orders yet.</div>
          ) : (
            <div className="table-responsive">
              <table className="table table-hover align-middle mb-0">
                <thead className="table-light">
                  <tr>
                    <th>PO Number</th>
                    <th>Vendor</th>
                    <th>Project</th>
                    <th>Date</th>
                    <th>Total (₹)</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {orders.map((po) => (
                    <tr key={po.id}>
                      <td className="fw-semibold">{po.po_number}</td>
                      <td>
                        <Link to={`/vendors/${po.vendor_id}`}>{po.vendor?.name || '—'}</Link>
                      </td>
                      <td>{po.project?.name || '—'}</td>
                      <td>{po.po_date?.slice(0, 10) || '—'}</td>
                      <td>₹{fmt(po.total_amount)}</td>
                      <td>
                        <span className={`badge bg-${STATUS_BADGE[po.status] || 'secondary'}`}>
                          {po.status?.replace(/_/g, ' ')}
                        </span>
                      </td>
                      <td>
                        <Link className="btn btn-sm btn-outline-secondary me-1" to={`/purchase-orders/${po.id}/edit`}>
                          Edit
                        </Link>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
