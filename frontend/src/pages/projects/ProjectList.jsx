import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { projectApi } from '../../api/endpoints';
import { usePaginated } from '../../hooks/usePaginated';
import PageHeader from '../../components/common/PageHeader';
import DataTable from '../../components/common/DataTable';
import StatusBadge from '../../components/common/StatusBadge';
import Pagination from '../../components/common/Pagination';

const fmt = (n) =>
  n ? Number(n).toLocaleString('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }) : '—';

export default function ProjectList() {
  const { data, meta, loading, setPage, applyFilters } = usePaginated(
    projectApi.list,
    { status: '', search: '' }
  );
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');

  const handleSearch = (e) => {
    e.preventDefault();
    applyFilters({ search, status });
  };

  const columns = [
    {
      key: 'name',
      label: 'Project',
      render: (row) => (
        <div>
          <Link to={`/projects/${row.id}`} className="fw-semibold text-decoration-none">{row.name}</Link>
          <div className="text-muted small">{row.location || '—'}</div>
        </div>
      ),
    },
    { key: 'client_name', label: 'Client', render: (r) => r.client_name || '—' },
    { key: 'status', label: 'Status', render: (r) => <StatusBadge status={r.status} /> },
    { key: 'budget', label: 'Budget', render: (r) => fmt(r.budget) },
    {
      key: 'completion_percentage',
      label: 'Progress',
      render: (r) => (
        <div className="d-flex align-items-center gap-2" style={{ minWidth: 120 }}>
          <div className="progress flex-grow-1" style={{ height: 6 }}>
            <div className="progress-bar bg-success" style={{ width: `${r.progress_percent || 0}%` }} />
          </div>
          <small className="text-muted">{r.progress_percent || 0}%</small>
        </div>
      ),
    },
    {
      key: 'actions',
      label: '',
      render: (r) => (
        <div className="d-flex gap-1">
          <Link to={`/projects/${r.id}`} className="btn btn-sm btn-outline-primary">View</Link>
          <Link to={`/projects/${r.id}/edit`} className="btn btn-sm btn-outline-secondary">Edit</Link>
        </div>
      ),
    },
  ];

  return (
    <div>
      <PageHeader
        title="Projects"
        action={<Link to="/projects/new" className="btn btn-primary"><i className="bi bi-plus-lg me-1" />New Project</Link>}
      />

      <div className="card border-0 shadow-sm mb-3">
        <div className="card-body py-2">
          <form onSubmit={handleSearch} className="row g-2 align-items-center">
            <div className="col">
              <input
                className="form-control form-control-sm"
                placeholder="Search projects..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
              />
            </div>
            <div className="col-auto">
              <select className="form-select form-select-sm" value={status} onChange={(e) => setStatus(e.target.value)}>
                <option value="">All Status</option>
                <option value="planned">Planned</option>
                <option value="in_progress">In Progress</option>
                <option value="on_hold">On Hold</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
            <div className="col-auto">
              <button type="submit" className="btn btn-sm btn-primary">Search</button>
            </div>
          </form>
        </div>
      </div>

      <div className="card border-0 shadow-sm">
        <div className="card-body p-0">
          <DataTable columns={columns} data={data} loading={loading} />
          <div className="px-3 pb-3">
            <Pagination meta={meta} onPageChange={setPage} />
          </div>
        </div>
      </div>
    </div>
  );
}
