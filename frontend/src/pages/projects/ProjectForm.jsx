import React, { useEffect } from 'react';
import { useForm } from 'react-hook-form';
import { useNavigate, useParams } from 'react-router-dom';
import { projectApi } from '../../api/endpoints';
import PageHeader from '../../components/common/PageHeader';
import { toast } from 'react-toastify';

const statusOptions = ['planned', 'in_progress', 'on_hold', 'completed', 'cancelled'];

export default function ProjectForm() {
  const { id } = useParams();
  const isEdit = Boolean(id);
  const navigate = useNavigate();
  const { register, handleSubmit, reset, formState: { errors, isSubmitting } } = useForm();

  useEffect(() => {
    if (isEdit) {
      projectApi.get(id).then((res) => {
        const p = res.data.data;
        reset({
          name: p.name,
          code: p.code,
          description: p.description,
          client_name: p.client_name,
          client_contact: p.client_contact,
          location: p.location,
          budget: p.budget,
          contract_value: p.contract_value,
          status: p.status,
          start_date: p.start_date,
          end_date: p.end_date,
          progress_percent: p.progress_percent,
        });
      });
    }
  }, [id, isEdit, reset]);

  const onSubmit = async (data) => {
    try {
      if (isEdit) {
        await projectApi.update(id, data);
        toast.success('Project updated.');
      } else {
        await projectApi.create(data);
        toast.success('Project created.');
      }
      navigate('/projects');
    } catch (err) {
      const errs = err.response?.data?.errors;
      if (errs) {
        Object.values(errs).flat().forEach((msg) => toast.error(msg));
      }
    }
  };

  return (
    <div>
      <PageHeader title={isEdit ? 'Edit Project' : 'New Project'} />
      <div className="card border-0 shadow-sm" style={{ maxWidth: 700 }}>
        <div className="card-body p-4">
          <form onSubmit={handleSubmit(onSubmit)}>
            <div className="row g-3">
              <div className="col-12">
                <label className="form-label fw-semibold">Project Name *</label>
                <input
                  className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                  {...register('name', { required: 'Project name is required' })}
                />
                {errors.name && <div className="invalid-feedback">{errors.name.message}</div>}
              </div>
              <div className="col-12">
                <label className="form-label fw-semibold">Description</label>
                <textarea rows={3} className="form-control" {...register('description')} />
              </div>
              <div className="col-md-6">
                <label className="form-label fw-semibold">Client Name</label>
                <input className="form-control" {...register('client_name')} />
              </div>
              <div className="col-md-6">
                <label className="form-label fw-semibold">Client Contact (email / phone)</label>
                <input className="form-control" {...register('client_contact')} />
              </div>
              <div className="col-md-6">
                <label className="form-label fw-semibold">Location</label>
                <input className="form-control" {...register('location')} />
              </div>
              <div className="col-md-6">
                <label className="form-label fw-semibold">Project Code</label>
                <input className="form-control" placeholder="e.g. PRJ-001" {...register('code')} />
              </div>
              <div className="col-md-4">
                <label className="form-label fw-semibold">Budget (₹)</label>
                <input type="number" min="0" step="0.01" className="form-control" {...register('budget')} />
              </div>
              <div className="col-md-4">
                <label className="form-label fw-semibold">Contract Value (₹)</label>
                <input type="number" min="0" step="0.01" className="form-control" {...register('contract_value')} />
              </div>
              <div className="col-md-4">
                <label className="form-label fw-semibold">Status</label>
                <select className="form-select" {...register('status')}>
                  {statusOptions.map((s) => (
                    <option key={s} value={s}>{s.replace(/_/g, ' ')}</option>
                  ))}
                </select>
              </div>
              <div className="col-md-4">
                <label className="form-label fw-semibold">Start Date</label>
                <input type="date" className="form-control" {...register('start_date')} />
              </div>
              <div className="col-md-4">
                <label className="form-label fw-semibold">End Date</label>
                <input type="date" className="form-control" {...register('end_date')} />
              </div>
              <div className="col-md-4">
                <label className="form-label fw-semibold">Progress (%)</label>
                <input type="number" min="0" max="100" className="form-control"
                  {...register('progress_percent')} />
              </div>
              <div className="col-12 d-flex gap-2">
                <button type="submit" className="btn btn-primary" disabled={isSubmitting}>
                  {isSubmitting ? <><span className="spinner-border spinner-border-sm me-2" />Saving...</> : 'Save Project'}
                </button>
                <button type="button" className="btn btn-outline-secondary" onClick={() => navigate('/projects')}>
                  Cancel
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}
