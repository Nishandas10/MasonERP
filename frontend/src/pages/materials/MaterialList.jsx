import React, { useState } from 'react';
import { materialApi } from '../../api/endpoints';
import { usePaginated } from '../../hooks/usePaginated';
import { useForm } from 'react-hook-form';
import PageHeader from '../../components/common/PageHeader';
import DataTable from '../../components/common/DataTable';
import Pagination from '../../components/common/Pagination';
import Modal from '../../components/common/Modal';
import { toast } from 'react-toastify';

function MaterialForm({ initial, onSave, onClose }) {
  const { register, handleSubmit, formState: { errors, isSubmitting } } = useForm({ defaultValues: initial || {} });

  const onSubmit = async (data) => {
    try {
      if (initial?.id) {
        await materialApi.update(initial.id, data);
        toast.success('Material updated.');
      } else {
        await materialApi.create(data);
        toast.success('Material created.');
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
          <label className="form-label fw-semibold">Code *</label>
          <input className={`form-control ${errors.code ? 'is-invalid' : ''}`}
            {...register('code', { required: true })} />
        </div>
        <div className="col-md-6">
          <label className="form-label fw-semibold">Category</label>
          <input className="form-control" placeholder="e.g. Cement, Steel, Aggregate..."
            {...register('category')} />
        </div>
        <div className="col-md-3">
          <label className="form-label fw-semibold">Unit *</label>
          <input className={`form-control ${errors.unit ? 'is-invalid' : ''}`}
            placeholder="kg, bag, sqm..." {...register('unit', { required: true })} />
        </div>
        <div className="col-md-3">
          <label className="form-label fw-semibold">Rate (₹)</label>
          <input type="number" min="0" step="0.01" className="form-control" {...register('standard_rate')} />
        </div>
        <div className="col-md-3">
          <label className="form-label fw-semibold">Current Stock</label>
          <input type="number" min="0" step="0.001" className="form-control" {...register('current_stock')} />
        </div>
        <div className="col-md-3">
          <label className="form-label fw-semibold">Min Stock</label>
          <input type="number" min="0" step="0.001" className="form-control" {...register('min_stock')} />
        </div>
        <div className="col-md-6">
          <label className="form-label fw-semibold">Status</label>
          <select className="form-select" {...register('status')}>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div className="col-12">
          <label className="form-label fw-semibold">Description</label>
          <textarea rows={2} className="form-control" {...register('description')} />
        </div>
        <div className="col-12 d-flex gap-2">
          <button type="submit" className="btn btn-primary" disabled={isSubmitting}>
            {isSubmitting ? 'Saving...' : 'Save Material'}
          </button>
          <button type="button" className="btn btn-outline-secondary" onClick={onClose}>Cancel</button>
        </div>
      </div>
    </form>
  );
}

export default function MaterialList() {
  const { data, meta, loading, setPage, refresh, applyFilters } = usePaginated(materialApi.list);
  const [modal, setModal] = useState(false);
  const [editing, setEditing] = useState(null);
  const [search, setSearch] = useState('');

  const columns = [
    { key: 'name', label: 'Material', render: (r) => <span className="fw-semibold">{r.name}</span> },
    { key: 'code', label: 'Code', render: (r) => r.code || '—' },
    { key: 'category', label: 'Category', render: (r) => r.category || '—' },
    { key: 'unit', label: 'Unit', render: (r) => r.unit || '—' },
    { key: 'standard_rate', label: 'Rate (₹)', render: (r) => r.standard_rate ? `₹${Number(r.standard_rate).toLocaleString('en-IN')}` : '—' },
    {
      key: 'stock',
      label: 'Stock',
      render: (r) => {
        const cur = Number(r.current_stock ?? 0);
        const min = Number(r.min_stock ?? 0);
        const low = cur <= min;
        return (
          <span className={low && cur > 0 ? 'text-danger fw-semibold' : low ? 'text-warning fw-semibold' : ''}>
            {cur} {r.unit}
            {low && <span className={`badge ms-1 ${cur === 0 ? 'bg-danger' : 'bg-warning text-dark'}`}>{cur === 0 ? 'Out' : 'Low'}</span>}
          </span>
        );
      },
    },
    { key: 'status', label: 'Status', render: (r) => (
        <span className={`badge ${r.status === 'active' ? 'bg-success' : 'bg-secondary'}`}>{r.status}</span>
      ),
    },
    {
      key: 'actions', label: '',
      render: (r) => (
        <button className="btn btn-sm btn-outline-secondary" onClick={() => { setEditing(r); setModal(true); }}>Edit</button>
      ),
    },
  ];

  return (
    <div>
      <PageHeader
        title="Materials"
        action={
          <button className="btn btn-primary" onClick={() => { setEditing(null); setModal(true); }}>
            <i className="bi bi-plus-lg me-1" />New Material
          </button>
        }
      />
      <div className="card border-0 shadow-sm mb-3">
        <div className="card-body py-2">
          <form className="row g-2" onSubmit={(e) => { e.preventDefault(); applyFilters({ search }); }}>
            <div className="col"><input className="form-control form-control-sm" placeholder="Search materials..."
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
      <Modal show={modal} onClose={() => setModal(false)} title={editing ? 'Edit Material' : 'New Material'} size="lg">
        <MaterialForm initial={editing} onSave={refresh} onClose={() => setModal(false)} />
      </Modal>
    </div>
  );
}
