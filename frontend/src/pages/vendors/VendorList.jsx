import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { vendorApi } from '../../api/endpoints';
import { usePaginated } from '../../hooks/usePaginated';
import { useForm } from 'react-hook-form';
import PageHeader from '../../components/common/PageHeader';
import DataTable from '../../components/common/DataTable';
import StatusBadge from '../../components/common/StatusBadge';
import Pagination from '../../components/common/Pagination';
import Modal from '../../components/common/Modal';
import { toast } from 'react-toastify';

function VendorForm({ initial, onSave, onClose }) {
  const { register, handleSubmit, formState: { errors, isSubmitting } } = useForm({ defaultValues: initial || {} });

  const onSubmit = async (data) => {
    try {
      if (initial?.id) {
        await vendorApi.update(initial.id, data);
        toast.success('Vendor updated.');
      } else {
        await vendorApi.create(data);
        toast.success('Vendor created.');
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
          <label className="form-label fw-semibold">Contact Person</label>
          <input className="form-control" {...register('contact_person')} />
        </div>
        <div className="col-md-6">
          <label className="form-label fw-semibold">Email</label>
          <input type="email" className="form-control" {...register('email')} />
        </div>
        <div className="col-md-6">
          <label className="form-label fw-semibold">Phone</label>
          <input className="form-control" {...register('phone')} />
        </div>
        <div className="col-md-6">
          <label className="form-label fw-semibold">GSTIN</label>
          <input className="form-control" placeholder="27ABCDE1234F1Z5" {...register('gstin')} />
        </div>
        <div className="col-md-6">
          <label className="form-label fw-semibold">Status</label>
          <select className="form-select" {...register('status')}>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="blacklisted">Blacklisted</option>
          </select>
        </div>
        <div className="col-12">
          <label className="form-label fw-semibold">Address</label>
          <textarea rows={2} className="form-control" {...register('address')} />
        </div>
        <div className="col-12 d-flex gap-2">
          <button type="submit" className="btn btn-primary" disabled={isSubmitting}>
            {isSubmitting ? 'Saving...' : 'Save Vendor'}
          </button>
          <button type="button" className="btn btn-outline-secondary" onClick={onClose}>Cancel</button>
        </div>
      </div>
    </form>
  );
}

export default function VendorList() {
  const { data, meta, loading, setPage, refresh, applyFilters } = usePaginated(vendorApi.list);
  const [modal, setModal] = useState(false);
  const [editing, setEditing] = useState(null);
  const [search, setSearch] = useState('');

  const openEdit = (vendor) => { setEditing(vendor); setModal(true); };
  const openCreate = () => { setEditing(null); setModal(true); };

  const columns = [
    { key: 'name', label: 'Vendor', render: (r) => <Link to={`/vendors/${r.id}`} className="fw-semibold text-decoration-none">{r.name}</Link> },
    { key: 'contact_person', label: 'Contact', render: (r) => r.contact_person || '—' },
    { key: 'phone', label: 'Phone', render: (r) => r.phone || '—' },
    { key: 'gstin', label: 'GSTIN', render: (r) => r.gstin || '—' },
    { key: 'status', label: 'Status', render: (r) => <StatusBadge status={r.status} /> },
    {
      key: 'actions', label: '',
      render: (r) => (
        <div className="d-flex gap-1">
          <Link to={`/vendors/${r.id}`} className="btn btn-sm btn-outline-primary">View</Link>
          <button className="btn btn-sm btn-outline-secondary" onClick={() => openEdit(r)}>Edit</button>
        </div>
      ),
    },
  ];

  return (
    <div>
      <PageHeader
        title="Vendors"
        action={<button className="btn btn-primary" onClick={openCreate}><i className="bi bi-plus-lg me-1" />New Vendor</button>}
      />
      <div className="card border-0 shadow-sm mb-3">
        <div className="card-body py-2">
          <form className="row g-2" onSubmit={(e) => { e.preventDefault(); applyFilters({ search }); }}>
            <div className="col"><input className="form-control form-control-sm" placeholder="Search vendors..."
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
      <Modal show={modal} onClose={() => setModal(false)} title={editing ? 'Edit Vendor' : 'New Vendor'} size="lg">
        <VendorForm initial={editing} onSave={refresh} onClose={() => setModal(false)} />
      </Modal>
    </div>
  );
}
