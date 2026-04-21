import React, { useEffect, useState, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { subcontractorApi, projectApi } from '../../api/endpoints';
import PageHeader from '../../components/common/PageHeader';
import StatusBadge from '../../components/common/StatusBadge';
import Modal from '../../components/common/Modal';
import { toast } from 'react-toastify';

const fmt = (n) =>
  n ? Number(n).toLocaleString('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }) : '₹0';

function ContractModal({ show, onClose, subId, projects, onCreated }) {
  const { register, handleSubmit, reset, formState: { isSubmitting } } = useForm();

  const onSubmit = async (data) => {
    try {
      await subcontractorApi.createContract(subId, data);
      toast.success('Contract created.');
      reset();
      onCreated();
      onClose();
    } catch (err) {
      toast.error(err.response?.data?.message || 'Failed.');
    }
  };

  return (
    <Modal show={show} onClose={onClose} title="New Contract" size="lg">
      <form onSubmit={handleSubmit(onSubmit)}>
        <div className="row g-3">
          <div className="col-md-6">
            <label className="form-label fw-semibold">Project *</label>
            <select className="form-select" {...register('project_id', { required: true })}>
              <option value="">Select project...</option>
              {projects.map((p) => <option key={p.id} value={p.id}>{p.name}</option>)}
            </select>
          </div>
          <div className="col-md-6">
            <label className="form-label fw-semibold">Scope of Work</label>
            <input className="form-control" {...register('scope_of_work')} />
          </div>
          <div className="col-md-4">
            <label className="form-label fw-semibold">Contract Value *</label>
            <input type="number" min="0" step="0.01" className="form-control"
              {...register('contract_value', { required: true })} />
          </div>
          <div className="col-md-4">
            <label className="form-label fw-semibold">Retention %</label>
            <input type="number" min="0" max="100" className="form-control"
              {...register('retention_percentage')} />
          </div>
          <div className="col-md-4">
            <label className="form-label fw-semibold">Start Date</label>
            <input type="date" className="form-control" {...register('start_date')} />
          </div>
          <div className="col-md-6">
            <label className="form-label fw-semibold">End Date</label>
            <input type="date" className="form-control" {...register('end_date')} />
          </div>
          <div className="col-12 d-flex gap-2">
            <button type="submit" className="btn btn-primary" disabled={isSubmitting}>
              {isSubmitting ? 'Saving...' : 'Create Contract'}
            </button>
            <button type="button" className="btn btn-outline-secondary" onClick={onClose}>Cancel</button>
          </div>
        </div>
      </form>
    </Modal>
  );
}

export default function SubcontractorDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [subcontractor, setSubcontractor] = useState(null);
  const [contracts, setContracts] = useState([]);
  const [bills, setBills] = useState([]);
  const [projects, setProjects] = useState([]);
  const [loading, setLoading] = useState(true);
  const [tab, setTab] = useState('info');
  const [contractModal, setContractModal] = useState(false);

  const load = useCallback(() => {
    Promise.all([
      subcontractorApi.get(id),
      subcontractorApi.contracts(id),
      subcontractorApi.bills(id),
    ])
      .then(([sRes, cRes, bRes]) => {
        setSubcontractor(sRes.data.data);
        setContracts(cRes.data.data || []);
        setBills(bRes.data.data || []);
      })
      .catch(() => toast.error('Failed to load.'))
      .finally(() => setLoading(false));
  }, [id]);

  useEffect(() => {
    load();
    projectApi.list({ per_page: 100 }).then((r) => setProjects(r.data.data || []));
  }, [load]);

  const handleApproveBill = async (billId) => {
    try {
      await subcontractorApi.approveBill(billId);
      toast.success('Bill approved.');
      load();
    } catch (err) {
      toast.error(err.response?.data?.message || 'Failed.');
    }
  };

  if (loading) return <div className="text-center py-5"><div className="spinner-border text-primary" /></div>;
  if (!subcontractor) return <div className="alert alert-danger">Not found.</div>;

  const s = subcontractor;

  return (
    <div>
      <PageHeader
        title={s.name}
        subtitle={s.trade || 'Subcontractor'}
        action={
          <div className="d-flex gap-2">
            <button className="btn btn-primary" onClick={() => setContractModal(true)}>New Contract</button>
            <button className="btn btn-outline-secondary"
              onClick={() => navigate(`/subcontractors/${id}/edit`)}>Edit</button>
            <button className="btn btn-outline-secondary" onClick={() => navigate('/subcontractors')}>Back</button>
          </div>
        }
      />

      <div className="row g-3 mb-4">
        {[
          { label: 'Status', value: <StatusBadge status={s.status} /> },
          { label: 'Phone', value: s.phone || '—' },
          { label: 'Email', value: s.email || '—' },
          { label: 'GST', value: s.gst_number || '—' },
        ].map(({ label, value }) => (
          <div key={label} className="col-md-3">
            <div className="card border-0 shadow-sm p-3 text-center">
              <div className="text-muted small">{label}</div>
              <div className="fw-bold mt-1">{value}</div>
            </div>
          </div>
        ))}
      </div>

      <ul className="nav nav-tabs mb-3">
        {['info', 'contracts', 'bills'].map((t) => (
          <li key={t} className="nav-item">
            <button className={`nav-link ${tab === t ? 'active' : ''}`} onClick={() => setTab(t)}>
              {t.charAt(0).toUpperCase() + t.slice(1)}
            </button>
          </li>
        ))}
      </ul>

      {tab === 'info' && (
        <div className="card border-0 shadow-sm">
          <div className="card-body">
            <dl className="row mb-0">
              <dt className="col-4 text-muted">Address</dt>
              <dd className="col-8">{s.address || '—'}</dd>
              <dt className="col-4 text-muted">Bank Name</dt>
              <dd className="col-8">{s.bank_name || '—'}</dd>
              <dt className="col-4 text-muted">Account Number</dt>
              <dd className="col-8">{s.bank_account || '—'}</dd>
              <dt className="col-4 text-muted">IFSC</dt>
              <dd className="col-8">{s.bank_ifsc || '—'}</dd>
              <dt className="col-4 text-muted">Notes</dt>
              <dd className="col-8">{s.notes || '—'}</dd>
            </dl>
          </div>
        </div>
      )}

      {tab === 'contracts' && (
        <div className="card border-0 shadow-sm">
          <div className="card-header bg-white fw-semibold">Contracts</div>
          <div className="table-responsive">
            <table className="table table-hover mb-0 align-middle">
              <thead className="table-light">
                <tr>
                  <th>Contract #</th>
                  <th>Project</th>
                  <th>Value</th>
                  <th>Retention</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                {contracts.length === 0 ? (
                  <tr><td colSpan={5} className="text-center text-muted py-3">No contracts</td></tr>
                ) : contracts.map((c) => (
                  <tr key={c.id}>
                    <td className="fw-semibold">{c.contract_number}</td>
                    <td>{c.project?.name || '—'}</td>
                    <td>{fmt(c.contract_value)}</td>
                    <td>{c.retention_percentage || 0}%</td>
                    <td><StatusBadge status={c.status} /></td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {tab === 'bills' && (
        <div className="card border-0 shadow-sm">
          <div className="card-header bg-white fw-semibold">Bills</div>
          <div className="table-responsive">
            <table className="table table-hover mb-0 align-middle">
              <thead className="table-light">
                <tr>
                  <th>Bill #</th>
                  <th>Amount</th>
                  <th>Net Payable</th>
                  <th>Balance Due</th>
                  <th>Status</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {bills.length === 0 ? (
                  <tr><td colSpan={6} className="text-center text-muted py-3">No bills</td></tr>
                ) : bills.map((b) => (
                  <tr key={b.id}>
                    <td className="fw-semibold">{b.bill_number}</td>
                    <td>{fmt(b.bill_amount)}</td>
                    <td>{fmt(b.net_payable)}</td>
                    <td className="fw-semibold text-danger">{fmt(b.balance_due)}</td>
                    <td><StatusBadge status={b.status} /></td>
                    <td>
                      {b.status === 'pending' && (
                        <button className="btn btn-sm btn-outline-success"
                          onClick={() => handleApproveBill(b.id)}>Approve</button>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      <ContractModal
        show={contractModal}
        onClose={() => setContractModal(false)}
        subId={id}
        projects={projects}
        onCreated={load}
      />
    </div>
  );
}
