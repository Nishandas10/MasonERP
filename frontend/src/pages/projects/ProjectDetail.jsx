import React, { useState, useEffect } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import { projectApi } from '../../api/endpoints';
import PageHeader from '../../components/common/PageHeader';
import StatusBadge from '../../components/common/StatusBadge';
import { toast } from 'react-toastify';

const fmt = (n) =>
  n ? Number(n).toLocaleString('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }) : '₹0';

export default function ProjectDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [project, setProject] = useState(null);
  const [milestones, setMilestones] = useState([]);
  const [boq, setBoq] = useState([]);
  const [loading, setLoading] = useState(true);
  const [tab, setTab] = useState('overview');

  useEffect(() => {
    Promise.all([
      projectApi.get(id),
      projectApi.milestones(id),
      projectApi.boq(id),
    ])
      .then(([pRes, mRes, bRes]) => {
        setProject(pRes.data.data);
        setMilestones(mRes.data.data || []);
        setBoq(bRes.data.data || []);
      })
      .catch(() => toast.error('Failed to load project.'))
      .finally(() => setLoading(false));
  }, [id]);

  if (loading) return <div className="text-center py-5"><div className="spinner-border text-primary" /></div>;
  if (!project) return <div className="alert alert-danger">Project not found.</div>;

  const boqTotal = boq.reduce((sum, item) => sum + (Number(item.amount) || 0), 0);

  return (
    <div>
      <PageHeader
        title={project.name}
        subtitle={project.location}
        action={
          <div className="d-flex gap-2">
            <Link to={`/projects/${id}/edit`} className="btn btn-outline-primary">Edit</Link>
            <button className="btn btn-outline-secondary" onClick={() => navigate('/projects')}>Back</button>
          </div>
        }
      />

      {/* Summary Row */}
      <div className="row g-3 mb-4">
        {[
          { label: 'Status', value: <StatusBadge status={project.status} /> },
          { label: 'Client', value: project.client_name || '—' },
          { label: 'Budget', value: fmt(project.budget) },
          { label: 'Progress', value: `${project.progress_percent || 0}%` },
        ].map(({ label, value }) => (
          <div key={label} className="col-md-3">
            <div className="card border-0 shadow-sm text-center p-3">
              <div className="text-muted small">{label}</div>
              <div className="fw-bold mt-1">{value}</div>
            </div>
          </div>
        ))}
      </div>

      {/* Tabs */}
      <ul className="nav nav-tabs mb-3">
        {['overview', 'milestones', 'boq'].map((t) => (
          <li key={t} className="nav-item">
            <button
              className={`nav-link ${tab === t ? 'active' : ''}`}
              onClick={() => setTab(t)}
            >
              {t.charAt(0).toUpperCase() + t.slice(1)}
            </button>
          </li>
        ))}
      </ul>

      {tab === 'overview' && (
        <div className="card border-0 shadow-sm">
          <div className="card-body">
            <div className="row g-3">
              <div className="col-md-6">
                <dl className="row mb-0">
                  <dt className="col-5 text-muted">Start Date</dt>
                  <dd className="col-7">{project.start_date || '—'}</dd>
                  <dt className="col-5 text-muted">End Date</dt>
                  <dd className="col-7">{project.end_date || '—'}</dd>
                  <dt className="col-5 text-muted">Contract Value</dt>
                  <dd className="col-7">{fmt(project.contract_value)}</dd>
                  <dt className="col-5 text-muted">Client Contact</dt>
                  <dd className="col-7">{project.client_contact || '—'}</dd>
                </dl>
              </div>
              <div className="col-md-6">
                <label className="text-muted small">Description</label>
                <p className="mt-1">{project.description || 'No description.'}</p>
              </div>
            </div>
          </div>
        </div>
      )}

      {tab === 'milestones' && (
        <div className="card border-0 shadow-sm">
          <div className="card-header bg-white d-flex justify-content-between align-items-center">
            <span className="fw-semibold">Milestones</span>
          </div>
          <div className="card-body p-0">
            <table className="table table-hover mb-0 align-middle">
              <thead className="table-light">
                <tr><th>Title</th><th>Due Date</th><th>Status</th><th>Completion</th></tr>
              </thead>
              <tbody>
                {milestones.length === 0 ? (
                  <tr><td colSpan={4} className="text-center text-muted py-3">No milestones yet</td></tr>
                ) : milestones.map((m) => (
                  <tr key={m.id}>
                    <td className="fw-semibold">{m.name}</td>
                    <td>{m.due_date || '—'}</td>
                    <td><StatusBadge status={m.status} /></td>
                    <td>{m.progress_percent || 0}%</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {tab === 'boq' && (
        <div className="card border-0 shadow-sm">
          <div className="card-header bg-white fw-semibold">Bill of Quantities</div>
          <div className="card-body p-0">
            <table className="table table-hover mb-0 align-middle">
              <thead className="table-light">
                <tr><th>Description</th><th>Unit</th><th>Qty</th><th>Rate</th><th>Amount</th></tr>
              </thead>
              <tbody>
                {boq.length === 0 ? (
                  <tr><td colSpan={5} className="text-center text-muted py-3">No BOQ items</td></tr>
                ) : boq.map((b) => (
                  <tr key={b.id}>
                    <td>{b.description}</td>
                    <td>{b.unit}</td>
                    <td>{b.quantity}</td>
                    <td>{fmt(b.rate)}</td>
                    <td className="fw-semibold">{fmt(b.amount)}</td>
                  </tr>
                ))}
              </tbody>
              <tfoot className="table-light">
                <tr>
                  <td colSpan={4} className="text-end fw-bold">Total</td>
                  <td className="fw-bold">{fmt(boqTotal)}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      )}
    </div>
  );
}
