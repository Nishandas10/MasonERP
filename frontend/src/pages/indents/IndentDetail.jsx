import React, { useEffect, useState, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { indentApi } from '../../api/endpoints';
import PageHeader from '../../components/common/PageHeader';
import StatusBadge from '../../components/common/StatusBadge';
import Modal from '../../components/common/Modal';
import { toast } from 'react-toastify';

export default function IndentDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [indent, setIndent] = useState(null);
  const [loading, setLoading] = useState(true);
  const [rejectModal, setRejectModal] = useState(false);
  const [rejectReason, setRejectReason] = useState('');

  const load = useCallback(() => {
    indentApi.get(id)
      .then((res) => setIndent(res.data.data))
      .catch(() => toast.error('Failed to load.'))
      .finally(() => setLoading(false));
  }, [id]);

  useEffect(() => { load(); }, [load]);

  const handleAction = async (action) => {
    try {
      if (action === 'submit') await indentApi.submit(id);
      if (action === 'approve') await indentApi.approve(id);
      toast.success(`Indent ${action}d.`);
      load();
    } catch (err) {
      toast.error(err.response?.data?.message || 'Action failed.');
    }
  };

  const handleReject = async () => {
    if (!rejectReason.trim()) return toast.error('Please provide a rejection reason.');
    try {
      await indentApi.reject(id, { reason: rejectReason });
      toast.success('Indent rejected.');
      setRejectModal(false);
      load();
    } catch (err) {
      toast.error(err.response?.data?.message || 'Action failed.');
    }
  };

  if (loading) return <div className="text-center py-5"><div className="spinner-border text-primary" /></div>;
  if (!indent) return <div className="alert alert-danger">Indent not found.</div>;

  return (
    <div>
      <PageHeader
        title={indent.indent_number}
        subtitle={indent.title}
        action={
          <div className="d-flex gap-2">
            {indent.status === 'draft' && (
              <>
                <button className="btn btn-info" onClick={() => handleAction('submit')}>Submit</button>
                <button className="btn btn-outline-secondary"
                  onClick={() => navigate(`/indents/${id}/edit`)}>Edit</button>
              </>
            )}
            {indent.status === 'submitted' && (
              <>
                <button className="btn btn-success" onClick={() => handleAction('approve')}>Approve</button>
                <button className="btn btn-danger" onClick={() => setRejectModal(true)}>Reject</button>
              </>
            )}
            <button className="btn btn-outline-secondary" onClick={() => navigate('/indents')}>Back</button>
          </div>
        }
      />

      <div className="row g-3 mb-4">
        {[
          { label: 'Status', value: <StatusBadge status={indent.status} /> },
          { label: 'Project', value: indent.project?.name || '—' },
          { label: 'Required Date', value: indent.required_date || '—' },
          { label: 'Created By', value: indent.creator?.name || '—' },
        ].map(({ label, value }) => (
          <div key={label} className="col-md-3">
            <div className="card border-0 shadow-sm p-3 text-center">
              <div className="text-muted small">{label}</div>
              <div className="fw-bold mt-1">{value}</div>
            </div>
          </div>
        ))}
      </div>

      {indent.notes && (
        <div className="alert alert-light border mb-3">
          <strong>Notes:</strong> {indent.notes}
        </div>
      )}

      <div className="card border-0 shadow-sm">
        <div className="card-header bg-white fw-semibold">Items</div>
        <div className="table-responsive">
          <table className="table table-hover mb-0 align-middle">
            <thead className="table-light">
              <tr>
                <th>#</th>
                <th>Material</th>
                <th>Quantity</th>
                <th>Unit</th>
                <th>Notes</th>
              </tr>
            </thead>
            <tbody>
              {(indent.items || []).map((item, idx) => (
                <tr key={item.id}>
                  <td className="text-muted">{idx + 1}</td>
                  <td className="fw-semibold">{item.material?.name || '—'}</td>
                  <td>{item.quantity}</td>
                  <td>{item.unit || '—'}</td>
                  <td className="text-muted">{item.notes || '—'}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      <Modal show={rejectModal} onClose={() => setRejectModal(false)} title="Reject Indent">
        <div className="mb-3">
          <label className="form-label fw-semibold">Reason for rejection *</label>
          <textarea
            rows={3}
            className="form-control"
            value={rejectReason}
            onChange={(e) => setRejectReason(e.target.value)}
            placeholder="Enter reason..."
          />
        </div>
        <div className="d-flex gap-2">
          <button className="btn btn-danger" onClick={handleReject}>Reject Indent</button>
          <button className="btn btn-outline-secondary" onClick={() => setRejectModal(false)}>Cancel</button>
        </div>
      </Modal>
    </div>
  );
}
