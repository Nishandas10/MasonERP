import React, { useState } from 'react';
import { equipmentApi } from '../../api/endpoints';
import { usePaginated } from '../../hooks/usePaginated';
import { useForm } from 'react-hook-form';
import PageHeader from '../../components/common/PageHeader';
import DataTable from '../../components/common/DataTable';
import StatusBadge from '../../components/common/StatusBadge';
import Pagination from '../../components/common/Pagination';
import Modal from '../../components/common/Modal';
import { toast } from 'react-toastify';

function EquipmentForm({ initial, onSave, onClose }) {
  const { register, handleSubmit, formState: { errors, isSubmitting } } = useForm({
    defaultValues: initial || { status: 'available' },
  });

  const onSubmit = async (data) => {
    try {
      if (initial?.id) {
        await equipmentApi.update(initial.id, data);
        toast.success('Equipment updated.');
      } else {
        await equipmentApi.create(data);
        toast.success('Equipment created.');
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
          <label className="form-label fw-semibold">Type *</label>
          <input className={`form-control ${errors.type ? 'is-invalid' : ''}`} placeholder="Excavator, Crane, Mixer..."
            {...register('type', { required: true })} />
        </div>
        <div className="col-md-4">
          <label className="form-label fw-semibold">Model</label>
          <input className="form-control" {...register('model')} />
        </div>
        <div className="col-md-4">
          <label className="form-label fw-semibold">Registration #</label>
          <input className="form-control" {...register('registration_number')} />
        </div>
        <div className="col-md-4">
          <label className="form-label fw-semibold">Status</label>
          <select className="form-select" {...register('status')}>
            <option value="available">Available</option>
            <option value="deployed">Deployed</option>
            <option value="maintenance">Maintenance</option>
            <option value="breakdown">Breakdown</option>
            <option value="retired">Retired</option>
          </select>
        </div>
        <div className="col-md-6">
          <label className="form-label fw-semibold">Purchase Date</label>
          <input type="date" className="form-control" {...register('purchase_date')} />
        </div>
        <div className="col-md-6">
          <label className="form-label fw-semibold">Purchase Value (₹)</label>
          <input type="number" min="0" step="0.01" className="form-control" {...register('purchase_value')} />
        </div>
        <div className="col-12 d-flex gap-2">
          <button type="submit" className="btn btn-primary" disabled={isSubmitting}>
            {isSubmitting ? 'Saving...' : 'Save Equipment'}
          </button>
          <button type="button" className="btn btn-outline-secondary" onClick={onClose}>Cancel</button>
        </div>
      </div>
    </form>
  );
}

export default function EquipmentList() {
  const { data, meta, loading, setPage, refresh, applyFilters } = usePaginated(equipmentApi.list);
  const [modal, setModal] = useState(false);
  const [editing, setEditing] = useState(null);
  const [search, setSearch] = useState('');

  const handleRelease = async (id) => {
    try {
      await equipmentApi.release(id);
      toast.success('Equipment released.');
      refresh();
    } catch (err) {
      toast.error(err.response?.data?.message || 'Failed.');
    }
  };

  const columns = [
    { key: 'name', label: 'Equipment', render: (r) => <span className="fw-semibold">{r.name}</span> },
    { key: 'type', label: 'Type', render: (r) => r.type || '—' },
    { key: 'model', label: 'Model', render: (r) => r.model || '—' },
    { key: 'registration_number', label: 'Reg #', render: (r) => r.registration_number || '—' },
    { key: 'status', label: 'Status', render: (r) => <StatusBadge status={r.status} /> },
    {
      key: 'actions', label: '',
      render: (r) => (
        <div className="d-flex gap-1">
          <button className="btn btn-sm btn-outline-secondary"
            onClick={() => { setEditing(r); setModal(true); }}>Edit</button>
          {r.status === 'deployed' && (
            <button className="btn btn-sm btn-outline-warning" onClick={() => handleRelease(r.id)}>Release</button>
          )}
        </div>
      ),
    },
  ];

  return (
    <div>
      <PageHeader
        title="Equipment"
        action={
          <button className="btn btn-primary" onClick={() => { setEditing(null); setModal(true); }}>
            <i className="bi bi-plus-lg me-1" />New Equipment
          </button>
        }
      />
      <div className="card border-0 shadow-sm mb-3">
        <div className="card-body py-2">
          <form className="row g-2" onSubmit={(e) => { e.preventDefault(); applyFilters({ search }); }}>
            <div className="col"><input className="form-control form-control-sm" placeholder="Search equipment..."
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
      <Modal show={modal} onClose={() => setModal(false)} title={editing ? 'Edit Equipment' : 'New Equipment'} size="lg">
        <EquipmentForm initial={editing} onSave={refresh} onClose={() => setModal(false)} />
      </Modal>
    </div>
  );
}
