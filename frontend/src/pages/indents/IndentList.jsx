import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { indentApi } from '../../api/endpoints';
import { usePaginated } from '../../hooks/usePaginated';
import PageHeader from '../../components/common/PageHeader';
import DataTable from '../../components/common/DataTable';
import StatusBadge from '../../components/common/StatusBadge';
import Pagination from '../../components/common/Pagination';
import { toast } from 'react-toastify';

export default function IndentList() {
  const { data, meta, loading, setPage, refresh, applyFilters } = usePaginated(
    indentApi.list, { status: '', search: '' }
  );
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');

  const handleAction = async (action, id, label) => {
    try {
      if (action === 'submit') await indentApi.submit(id);
      if (action === 'approve') await indentApi.approve(id);
      toast.success(`Indent ${label}.`);
      refresh();
    } catch (err) {
      toast.error(err.response?.data?.message || 'Action failed.');
    }
  };

  const columns = [
    {
      key: 'indent_number',
      label: 'Indent #',
      render: (r) => (
        <Link to={`/indents/${r.id}`} className="fw-semibold text-decoration-none">
          {r.indent_number}
        </Link>
      ),
    },
    { key: 'project', label: 'Project', render: (r) => r.project?.name || '—' },
    { key: 'status', label: 'Status', render: (r) => <StatusBadge status={r.status} /> },
    {
      key: 'required_by_date',
      label: 'Required By',
      render: (r) => String(r.required_by_date || '').slice(0, 10) || '—',
    },
    {
      key: 'actions',
      label: '',
      render: (r) => (
        <div className="d-flex gap-1 flex-wrap">
          <Link to={`/indents/${r.id}`} className="btn btn-sm btn-outline-primary">View</Link>
          {r.status === 'draft' && (
            <>
              <Link to={`/indents/${r.id}/edit`} className="btn btn-sm btn-outline-secondary">Edit</Link>
              <button className="btn btn-sm btn-outline-info" onClick={() => handleAction('submit', r.id, 'submitted')}>
                Submit
              </button>
            </>
          )}
          {r.status === 'submitted' && (
            <button className="btn btn-sm btn-outline-success" onClick={() => handleAction('approve', r.id, 'approved')}>
              Approve
            </button>
          )}
        </div>
      ),
    },
  ];

  return (
    <div>
      <PageHeader
        title="Material Indents"
        action={<Link to="/indents/new" className="btn btn-primary"><i className="bi bi-plus-lg me-1" />New Indent</Link>}
      />

      <div className="card border-0 shadow-sm mb-3">
        <div className="card-body py-2">
          <form
            className="row g-2 align-items-center"
            onSubmit={(e) => { e.preventDefault(); applyFilters({ search, status }); }}
          >
            <div className="col">
              <input className="form-control form-control-sm" placeholder="Search indents..."
                value={search} onChange={(e) => setSearch(e.target.value)} />
            </div>
            <div className="col-auto">
              <select className="form-select form-select-sm" value={status}
                onChange={(e) => setStatus(e.target.value)}>
                <option value="">All Status</option>
                <option value="draft">Draft</option>
                <option value="submitted">Submitted</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
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
