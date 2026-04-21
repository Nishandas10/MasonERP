import React, { useState, useEffect } from 'react';
import { expenseApi, projectApi } from '../../api/endpoints';
import { usePaginated } from '../../hooks/usePaginated';
import { useForm } from 'react-hook-form';
import PageHeader from '../../components/common/PageHeader';
import DataTable from '../../components/common/DataTable';
import StatusBadge from '../../components/common/StatusBadge';
import Pagination from '../../components/common/Pagination';
import Modal from '../../components/common/Modal';
import { toast } from 'react-toastify';

const fmt = (n) =>
  n ? Number(n).toLocaleString('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }) : '—';

function ExpenseForm({ onSave, onClose }) {
  const [categories, setCategories] = useState([]);
  const [projects, setProjects] = useState([]);
  const { register, handleSubmit, formState: { errors, isSubmitting } } = useForm();

  useEffect(() => {
    expenseApi.categories().then((r) => setCategories(r.data.data || []));
    projectApi.list({ per_page: 100 }).then((r) => setProjects(r.data.data || []));
  }, []);

  const onSubmit = async (data) => {
    try {
      await expenseApi.create(data);
      toast.success('Expense recorded.');
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
        <div className="col-12">
          <label className="form-label fw-semibold">Description *</label>
          <input className={`form-control ${errors.description ? 'is-invalid' : ''}`}
            {...register('description', { required: true })} />
        </div>
        <div className="col-md-6">
          <label className="form-label fw-semibold">Amount (₹) *</label>
          <input type="number" min="0" step="0.01" className={`form-control ${errors.amount ? 'is-invalid' : ''}`}
            {...register('amount', { required: true, min: 0.01 })} />
        </div>
        <div className="col-md-6">
          <label className="form-label fw-semibold">Expense Date *</label>
          <input type="date" className={`form-control ${errors.expense_date ? 'is-invalid' : ''}`}
            {...register('expense_date', { required: true })} />
        </div>
        <div className="col-md-6">
          <label className="form-label fw-semibold">Category *</label>
          <select className={`form-select ${errors.expense_category_id ? 'is-invalid' : ''}`}
            {...register('expense_category_id', { required: true })}>
            <option value="">Select category...</option>
            {categories.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
          </select>
        </div>
        <div className="col-md-6">
          <label className="form-label fw-semibold">Project *</label>
          <select className={`form-select ${errors.project_id ? 'is-invalid' : ''}`}
            {...register('project_id', { required: true })}>
            <option value="">Select project...</option>
            {projects.map((p) => <option key={p.id} value={p.id}>{p.name}</option>)}
          </select>
        </div>
        <div className="col-md-6">
          <label className="form-label fw-semibold">Payment Mode</label>
          <select className="form-select" {...register('payment_mode')}>
            <option value="">Select...</option>
            <option value="cash">Cash</option>
            <option value="bank">Bank Transfer</option>
            <option value="cheque">Cheque</option>
            <option value="upi">UPI</option>
          </select>
        </div>
        <div className="col-md-6">
          <label className="form-label fw-semibold">Reference Number</label>
          <input className="form-control" {...register('reference_number')} />
        </div>
        <div className="col-12 d-flex gap-2">
          <button type="submit" className="btn btn-primary" disabled={isSubmitting}>
            {isSubmitting ? 'Saving...' : 'Record Expense'}
          </button>
          <button type="button" className="btn btn-outline-secondary" onClick={onClose}>Cancel</button>
        </div>
      </div>
    </form>
  );
}

export default function ExpenseList() {
  const { data, meta, loading, setPage, refresh, applyFilters } = usePaginated(expenseApi.list);
  const [modal, setModal] = useState(false);
  const [search, setSearch] = useState('');

  const handleApprove = async (id) => {
    try {
      await expenseApi.approve(id);
      toast.success('Expense approved.');
      refresh();
    } catch (err) {
      toast.error(err.response?.data?.message || 'Failed.');
    }
  };

  const columns = [
    { key: 'title', label: 'Description', render: (r) => <span className="fw-semibold">{r.description}</span> },
    { key: 'category', label: 'Category', render: (r) => r.category?.name || '—' },
    { key: 'project', label: 'Project', render: (r) => r.project?.name || '—' },
    { key: 'amount', label: 'Amount', render: (r) => fmt(r.amount) },
    { key: 'expense_date', label: 'Date', render: (r) => r.expense_date || '—' },
    { key: 'status', label: 'Status', render: (r) => <StatusBadge status={r.status} /> },
    {
      key: 'actions', label: '',
      render: (r) =>
        r.status === 'pending' ? (
          <button className="btn btn-sm btn-outline-success" onClick={() => handleApprove(r.id)}>Approve</button>
        ) : null,
    },
  ];

  return (
    <div>
      <PageHeader
        title="Expenses"
        action={
          <button className="btn btn-primary" onClick={() => setModal(true)}>
            <i className="bi bi-plus-lg me-1" />Record Expense
          </button>
        }
      />
      <div className="card border-0 shadow-sm mb-3">
        <div className="card-body py-2">
          <form className="row g-2" onSubmit={(e) => { e.preventDefault(); applyFilters({ search }); }}>
            <div className="col"><input className="form-control form-control-sm" placeholder="Search expenses..."
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
      <Modal show={modal} onClose={() => setModal(false)} title="Record Expense" size="lg">
        <ExpenseForm onSave={refresh} onClose={() => setModal(false)} />
      </Modal>
    </div>
  );
}
