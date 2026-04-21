import React, { useState } from 'react';
import { userApi } from '../../api/endpoints';
import { usePaginated } from '../../hooks/usePaginated';
import { useForm } from 'react-hook-form';
import PageHeader from '../../components/common/PageHeader';
import DataTable from '../../components/common/DataTable';
import StatusBadge from '../../components/common/StatusBadge';
import Pagination from '../../components/common/Pagination';
import Modal from '../../components/common/Modal';
import { toast } from 'react-toastify';

function UserForm({ initial, onSave, onClose }) {
  const { register, handleSubmit, formState: { errors, isSubmitting } } = useForm({ defaultValues: initial || {} });

  const onSubmit = async (data) => {
    try {
      if (initial?.id) {
        await userApi.update(initial.id, data);
        toast.success('User updated.');
      } else {
        await userApi.create(data);
        toast.success('User created.');
      }
      onSave();
      onClose();
    } catch (err) {
      const errs = err.response?.data?.errors;
      if (errs) Object.values(errs).flat().forEach((m) => toast.error(m));
      else toast.error('Failed to save.');
    }
  };

  return (
    <form onSubmit={handleSubmit(onSubmit)}>
      <div className="row g-3">
        <div className="col-md-6">
          <label className="form-label fw-semibold">Full Name *</label>
          <input className={`form-control ${errors.name ? 'is-invalid' : ''}`}
            {...register('name', { required: true })} />
        </div>
        <div className="col-md-6">
          <label className="form-label fw-semibold">Email *</label>
          <input type="email" className={`form-control ${errors.email ? 'is-invalid' : ''}`}
            {...register('email', { required: true })} />
        </div>
        {!initial && (
          <div className="col-md-6">
            <label className="form-label fw-semibold">Password *</label>
            <input type="password" className={`form-control ${errors.password ? 'is-invalid' : ''}`}
              {...register('password', { required: !initial })} />
          </div>
        )}
        <div className="col-md-6">
          <label className="form-label fw-semibold">Phone</label>
          <input className="form-control" {...register('phone')} />
        </div>
        <div className="col-md-6">
          <label className="form-label fw-semibold">Status</label>
          <select className="form-select" {...register('status')}>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div className="col-12 d-flex gap-2">
          <button type="submit" className="btn btn-primary" disabled={isSubmitting}>
            {isSubmitting ? 'Saving...' : initial ? 'Update User' : 'Create User'}
          </button>
          <button type="button" className="btn btn-outline-secondary" onClick={onClose}>Cancel</button>
        </div>
      </div>
    </form>
  );
}

export default function UserList() {
  const { data, meta, loading, setPage, refresh, applyFilters } = usePaginated(userApi.list);
  const [modal, setModal] = useState(false);
  const [editing, setEditing] = useState(null);
  const [search, setSearch] = useState('');

  const columns = [
    {
      key: 'name',
      label: 'User',
      render: (r) => (
        <div>
          <div className="fw-semibold">{r.name}</div>
          <div className="text-muted small">{r.email}</div>
        </div>
      ),
    },
    { key: 'role', label: 'Role', render: (r) => r.role?.name || '—' },
    { key: 'phone', label: 'Phone', render: (r) => r.phone || '—' },
    { key: 'status', label: 'Status', render: (r) => <StatusBadge status={r.status} /> },
    {
      key: 'last_login', label: 'Last Login',
      render: (r) => r.last_login_at ? new Date(r.last_login_at).toLocaleDateString() : '—',
    },
    {
      key: 'actions', label: '',
      render: (r) => (
        <button className="btn btn-sm btn-outline-secondary"
          onClick={() => { setEditing(r); setModal(true); }}>Edit</button>
      ),
    },
  ];

  return (
    <div>
      <PageHeader
        title="Users"
        action={
          <button className="btn btn-primary" onClick={() => { setEditing(null); setModal(true); }}>
            <i className="bi bi-plus-lg me-1" />New User
          </button>
        }
      />
      <div className="card border-0 shadow-sm mb-3">
        <div className="card-body py-2">
          <form className="row g-2" onSubmit={(e) => { e.preventDefault(); applyFilters({ search }); }}>
            <div className="col"><input className="form-control form-control-sm" placeholder="Search users..."
              value={search} onChange={(e) => setSearch(e.target.value)} /></div>
            <div className="col-auto"><button type="submit" className="btn btn-sm btn-primary">Search</button></div>
          </form>
        </div>
      </div>
      <div className="card border-0 shadow-sm">
        <div className="card-body p-0">
          <DataTable columns={columns} data={data} loading={loading} />
          <div className="px-3 pb-3"><Pagination meta={meta} onPageChange={setPage} /></div>
        </div>
      </div>
      <Modal show={modal} onClose={() => setModal(false)} title={editing ? 'Edit User' : 'New User'} size="lg">
        <UserForm initial={editing} onSave={refresh} onClose={() => setModal(false)} />
      </Modal>
    </div>
  );
}
