import React, { useEffect } from 'react';
import { useForm } from 'react-hook-form';
import { useNavigate, useParams } from 'react-router-dom';
import { subcontractorApi } from '../../api/endpoints';
import PageHeader from '../../components/common/PageHeader';
import { toast } from 'react-toastify';

export default function SubcontractorForm() {
  const { id } = useParams();
  const isEdit = Boolean(id);
  const navigate = useNavigate();
  const { register, handleSubmit, reset, formState: { errors, isSubmitting } } = useForm();

  useEffect(() => {
    if (isEdit) {
      subcontractorApi.get(id).then((res) => {
        const s = res.data.data;
        reset({
          name: s.name,
          email: s.email,
          phone: s.phone,
          trade: s.trade,
          address: s.address,
          gst_number: s.gst_number,
          bank_name: s.bank_name,
          bank_account: s.bank_account,
          bank_ifsc: s.bank_ifsc,
          status: s.status,
          notes: s.notes,
        });
      });
    }
  }, [id, isEdit, reset]);

  const onSubmit = async (data) => {
    try {
      if (isEdit) {
        await subcontractorApi.update(id, data);
        toast.success('Subcontractor updated.');
      } else {
        await subcontractorApi.create(data);
        toast.success('Subcontractor created.');
      }
      navigate('/subcontractors');
    } catch (err) {
      const errs = err.response?.data?.errors;
      if (errs) Object.values(errs).flat().forEach((m) => toast.error(m));
      else toast.error(err.response?.data?.message || 'Failed to save.');
    }
  };

  return (
    <div>
      <PageHeader title={isEdit ? 'Edit Subcontractor' : 'New Subcontractor'} />
      <div className="card border-0 shadow-sm" style={{ maxWidth: 720 }}>
        <div className="card-body p-4">
          <form onSubmit={handleSubmit(onSubmit)}>
            <div className="row g-3">
              <div className="col-md-6">
                <label className="form-label fw-semibold">Full Name *</label>
                <input
                  className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                  {...register('name', { required: 'Name is required' })}
                />
                {errors.name && <div className="invalid-feedback">{errors.name.message}</div>}
              </div>
              <div className="col-md-6">
                <label className="form-label fw-semibold">Trade / Specialization</label>
                <input className="form-control" placeholder="e.g. Masonry, Plumbing..."
                  {...register('trade')} />
              </div>
              <div className="col-md-6">
                <label className="form-label fw-semibold">Email</label>
                <input type="email" className="form-control" {...register('email')} />
              </div>
              <div className="col-md-6">
                <label className="form-label fw-semibold">Phone</label>
                <input className="form-control" {...register('phone')} />
              </div>
              <div className="col-12">
                <label className="form-label fw-semibold">Address</label>
                <textarea rows={2} className="form-control" {...register('address')} />
              </div>
              <div className="col-md-4">
                <label className="form-label fw-semibold">GST Number</label>
                <input className="form-control" {...register('gst_number')} />
              </div>
              <div className="col-md-4">
                <label className="form-label fw-semibold">Bank Name</label>
                <input className="form-control" {...register('bank_name')} />
              </div>
              <div className="col-md-4">
                <label className="form-label fw-semibold">Account Number</label>
                <input className="form-control" {...register('bank_account')} />
              </div>
              <div className="col-md-4">
                <label className="form-label fw-semibold">IFSC Code</label>
                <input className="form-control" {...register('bank_ifsc')} />
              </div>
              <div className="col-md-4">
                <label className="form-label fw-semibold">Status</label>
                <select className="form-select" {...register('status')}>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                  <option value="blacklisted">Blacklisted</option>
                </select>
              </div>
              <div className="col-12">
                <label className="form-label fw-semibold">Notes</label>
                <textarea rows={2} className="form-control" {...register('notes')} />
              </div>
              <div className="col-12 d-flex gap-2">
                <button type="submit" className="btn btn-primary" disabled={isSubmitting}>
                  {isSubmitting ? <><span className="spinner-border spinner-border-sm me-2" />Saving...</> : 'Save'}
                </button>
                <button type="button" className="btn btn-outline-secondary"
                  onClick={() => navigate('/subcontractors')}>Cancel</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}
