import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { subcontractorApi } from '../../api/endpoints';
import { usePaginated } from '../../hooks/usePaginated';
import PageHeader from '../../components/common/PageHeader';
import DataTable from '../../components/common/DataTable';
import StatusBadge from '../../components/common/StatusBadge';
import Pagination from '../../components/common/Pagination';

export default function SubcontractorList() {
  const { data, meta, loading, setPage, applyFilters } = usePaginated(
    subcontractorApi.list, { status: '', search: '' }
  );
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');

  const columns = [
    {
      key: 'name',
      label: 'Subcontractor',
      render: (r) => (
        <div>
          <Link to={`/subcontractors/${r.id}`} className="fw-semibold text-decoration-none">{r.name}</Link>
          <div className="text-muted small">{r.email || '—'}</div>
        </div>
      ),
    },
    { key: 'phone', label: 'Phone', render: (r) => r.phone || '—' },
    { key: 'trade', label: 'Trade', render: (r) => r.trade || '—' },
    { key: 'status', label: 'Status', render: (r) => <StatusBadge status={r.status} /> },
    {
      key: 'actions',
      label: '',
      render: (r) => (
        <div className="d-flex gap-1">
          <Link to={`/subcontractors/${r.id}`} className="btn btn-sm btn-outline-primary">View</Link>
          <Link to={`/subcontractors/${r.id}/edit`} className="btn btn-sm btn-outline-secondary">Edit</Link>
        </div>
      ),
    },
  ];

  return (
    <div>
      <PageHeader
        title="Subcontractors"
        action={<Link to="/subcontractors/new" className="btn btn-primary"><i className="bi bi-plus-lg me-1" />New Subcontractor</Link>}
      />

      <div className="card border-0 shadow-sm mb-3">
        <div className="card-body py-2">
          <form
            className="row g-2 align-items-center"
            onSubmit={(e) => { e.preventDefault(); applyFilters({ search, status }); }}
          >
            <div className="col">
              <input className="form-control form-control-sm" placeholder="Search subcontractors..."
                value={search} onChange={(e) => setSearch(e.target.value)} />
            </div>
            <div className="col-auto">
              <select className="form-select form-select-sm" value={status}
                onChange={(e) => setStatus(e.target.value)}>
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="blacklisted">Blacklisted</option>
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
