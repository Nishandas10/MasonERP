import React, { useEffect } from 'react';
import { useForm } from 'react-hook-form';
import { useNavigate, useParams } from 'react-router-dom';
import { vendorApi } from '../../api/endpoints';
import PageHeader from '../../components/common/PageHeader';
import { toast } from 'react-toastify';

export default function VendorForm() {
  const { id } = useParams();
  const isEdit = Boolean(id);
  const navigate = useNavigate();

  const { register, handleSubmit, reset, formState: { errors, isSubmitting } } = useForm({
    defaultValues: {
      name: '', code: '', contact_person: '', email: '', phone: '',
      address: '', city: '', state: '', gstin: '', pan: '',
      bank_name: '', bank_account: '', bank_ifsc: '', status: 'active',
    },
  });

  useEffect(() => {
    if (isEdit) {
      vendorApi.get(id)
        .then((res) => {
          const v = res.data.data;
          reset({
            name: v.name || '',
            code: v.code || '',
            contact_person: v.contact_person || '',
            email: v.email || '',
            phone: v.phone || '',
            address: v.address || '',
            city: v.city || '',
            state: v.state || '',
            gstin: v.gstin || '',
            pan: v.pan || '',
            bank_name: v.bank_name || '',
            bank_account: v.bank_account || '',
            bank_ifsc: v.bank_ifsc || '',
            status: v.status || 'active',
          });
        })
        .catch(() => toast.error('Failed to load vendor.'));
    }
  }, [id, isEdit, reset]);

  const onSubmit = async (data) => {
    try {
      if (isEdit) {
        await vendorApi.update(id, data);
        toast.success('Vendor updated.');
        navigate(`/vendors/${id}`);
      } else {
        const res = await vendorApi.create(data);
        toast.success('Vendor created.');
        navigate(`/vendors/${res.data.data.id}`);
      }
    } catch (err) {
      const errs = err.response?.data?.errors;
      if (errs) Object.values(errs).flat().forEach((m) => toast.error(m));
      else toast.error(err.response?.data?.message || 'Failed to save.');
    }
  };

  return (
    <div>
      <PageHeader
        title={isEdit ? 'Edit Vendor' : 'New Vendor'}
        action={
          <button type="button" className="btn btn-outline-secondary"
            onClick={() => navigate(isEdit ? `/vendors/${id}` : '/vendors')}>
            Cancel
          </button>
        }
      />
      <div className="card border-0 shadow-sm">
        <div className="card-body p-4">
          <form onSubmit={handleSubmit(onSubmit)}>
            <h6 className="fw-bold mb-3 text-muted text-uppercase" style={{ fontSize: 12, letterSpacing: 1 }}>Basic Info</h6>
            <div className="row g-3 mb-4">
              <div className="col-md-6">
                <label className="form-label fw-semibold">Name *</label>
                <input className={`form-control ${errors.name ? 'is-invalid' : ''}`}
                  {...register('name', { required: 'Name is required' })} />
                {errors.name && <div className="invalid-feedback">{errors.name.message}</div>}
              </div>
              <div className="col-md-3">
                <label className="form-label fw-semibold">Code</label>
                <input className="form-control" {...register('code')} />
              </div>
              <div className="col-md-3">
                <label className="form-label fw-semibold">Status</label>
                <select className="form-select" {...register('status')}>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                  <option value="blacklisted">Blacklisted</option>
                </select>
              </div>
              <div className="col-md-4">
                <label className="form-label fw-semibold">Contact Person</label>
                <input className="form-control" {...register('contact_person')} />
              </div>
              <div className="col-md-4">
                <label className="form-label fw-semibold">Phone</label>
                <input className="form-control" {...register('phone')} />
              </div>
              <div className="col-md-4">
                <label className="form-label fw-semibold">Email</label>
                <input type="email" className="form-control" {...register('email')} />
              </div>
              <div className="col-12">
                <label className="form-label fw-semibold">Address</label>
                <textarea rows={2} className="form-control" {...register('address')} />
              </div>
              <div className="col-md-4">
                <label className="form-label fw-semibold">City</label>
                <input className="form-control" {...register('city')} />
              </div>
              <div className="col-md-4">
                <label className="form-label fw-semibold">State</label>
                <input className="form-control" {...register('state')} />
              </div>
            </div>

            <h6 className="fw-bold mb-3 text-muted text-uppercase" style={{ fontSize: 12, letterSpacing: 1 }}>Tax & Compliance</h6>
            <div className="row g-3 mb-4">
              <div className="col-md-4">
                <label className="form-label fw-semibold">GSTIN</label>
                <input className="form-control" placeholder="27ABCDE1234F1Z5" {...register('gstin')} />
              </div>
              <div className="col-md-4">
                <label className="form-label fw-semibold">PAN</label>
                <input className="form-control" placeholder="ABCDE1234F" {...register('pan')} />
              </div>
            </div>

            <h6 className="fw-bold mb-3 text-muted text-uppercase" style={{ fontSize: 12, letterSpacing: 1 }}>Bank Details</h6>
            <div className="row g-3 mb-4">
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
            </div>

            <div className="d-flex gap-2">
              <button type="submit" className="btn btn-primary" disabled={isSubmitting}>
                {isSubmitting ? <><span className="spinner-border spinner-border-sm me-2" />Saving...</> : isEdit ? 'Update Vendor' : 'Create Vendor'}
              </button>
              <button type="button" className="btn btn-outline-secondary"
                onClick={() => navigate(isEdit ? `/vendors/${id}` : '/vendors')}>
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}
