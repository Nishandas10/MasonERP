import React, { useState } from 'react';
import { laborApi } from '../../api/endpoints';
import { usePaginated } from '../../hooks/usePaginated';
import { useForm } from 'react-hook-form';
import PageHeader from '../../components/common/PageHeader';
import DataTable from '../../components/common/DataTable';
import StatusBadge from '../../components/common/StatusBadge';
import Pagination from '../../components/common/Pagination';
import Modal from '../../components/common/Modal';
import { toast } from 'react-toastify';

function LaborForm({ initial, onSave, onClose }) {
  const { register, handleSubmit, formState: { errors, isSubmitting } } = useForm({
    defaultValues: initial || { status: 'active' },
  });

  const onSubmit = async (data) => {
    try {
      if (initial?.id) {
        await laborApi.update(initial.id, data);
        toast.success('Laborer updated.');
      } else {
        await laborApi.create(data);
        toast.success('Laborer added.');
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
          <label className="form-label fw-semibold">Name *</label>
          <input className={`form-control ${errors.name ? 'is-invalid' : ''}`}
            {...register('name', { required: true })} />
        </div>
        <div className="col-md-6">
          <label className="form-label fw-semibold">Trade / Skill</label>
          <input className="form-control" placeholder="Mason, Helper, Welder..."
            {...register('trade')} />
        </div>
        <div className="col-md-6">
          <label className="form-label fw-semibold">Phone</label>
          <input className="form-control" {...register('phone')} />
        </div>
        <div className="col-md-6">
          <label className="form-label fw-semibold">Daily Rate (₹)</label>
          <input type="number" min="0" step="0.01" className="form-control" {...register('daily_rate')} />
        </div>
        <div className="col-md-6">
          <label className="form-label fw-semibold">Aadhaar Number</label>
          <input className="form-control" {...register('aadhaar')} />
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
            {isSubmitting ? 'Saving...' : 'Save'}
          </button>
          <button type="button" className="btn btn-outline-secondary" onClick={onClose}>Cancel</button>
        </div>
      </div>
    </form>
  );
}

export default function LaborList() {
  const { data, meta, loading, setPage, refresh, applyFilters } = usePaginated(laborApi.list);
  const [modal, setModal] = useState(false);
  const [editing, setEditing] = useState(null);
  const [search, setSearch] = useState('');

  const columns = [
    { key: 'name', label: 'Name', render: (r) => <span className="fw-semibold">{r.name}</span> },
    { key: 'trade', label: 'Trade', render: (r) => r.trade || '—' },
    { key: 'phone', label: 'Phone', render: (r) => r.phone || '—' },
    { key: 'daily_rate', label: 'Daily Rate', render: (r) => r.daily_rate ? `₹${r.daily_rate}` : '—' },
    { key: 'status', label: 'Status', render: (r) => <StatusBadge status={r.status} /> },
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
        title="Labor"
        action={
          <button className="btn btn-primary" onClick={() => { setEditing(null); setModal(true); }}>
            <i className="bi bi-plus-lg me-1" />Add Laborer
          </button>
        }
      />
      <div className="card border-0 shadow-sm mb-3">
        <div className="card-body py-2">
          <form className="row g-2" onSubmit={(e) => { e.preventDefault(); applyFilters({ search }); }}>
            <div className="col"><input className="form-control form-control-sm" placeholder="Search labor..."
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
      <Modal show={modal} onClose={() => setModal(false)} title={editing ? 'Edit Laborer' : 'Add Laborer'} size="lg">
        <LaborForm initial={editing} onSave={refresh} onClose={() => setModal(false)} />
      </Modal>
    </div>
  );
}
