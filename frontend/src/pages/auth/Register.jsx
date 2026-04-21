import React, { useEffect } from 'react';
import { useForm } from 'react-hook-form';
import { useDispatch, useSelector } from 'react-redux';
import { Link, useNavigate } from 'react-router-dom';
import { registerUser, clearError } from '../../store/slices/authSlice';
import { toast } from 'react-toastify';

export default function Register() {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { loading, error, token } = useSelector((s) => s.auth);

  const { register, handleSubmit, watch, formState: { errors } } = useForm();
  const password = watch('password');

  useEffect(() => {
    if (token) navigate('/dashboard', { replace: true });
    return () => dispatch(clearError());
  }, [token, navigate, dispatch]);

  const onSubmit = async (data) => {
    const res = await dispatch(registerUser(data));
    if (registerUser.fulfilled.match(res)) {
      toast.success('Company registered successfully!');
      navigate('/dashboard');
    }
  };

  const fieldError = (field) =>
    error?.[field] ? <div className="invalid-feedback d-block">{error[field][0]}</div> : null;

  return (
    <div className="min-vh-100 d-flex align-items-center justify-content-center bg-light py-5">
      <div style={{ width: '100%', maxWidth: 480 }}>
        <div className="text-center mb-4">
          <div className="fs-1 mb-1">🏗️</div>
          <h3 className="fw-bold">Create Company Account</h3>
          <p className="text-muted">Register your construction company</p>
        </div>
        <div className="card shadow-sm border-0">
          <div className="card-body p-4">
            {error?.general && (
              <div className="alert alert-danger py-2">{error.general[0]}</div>
            )}
            <form onSubmit={handleSubmit(onSubmit)}>
              <div className="mb-3">
                <label className="form-label fw-semibold">Company Name</label>
                <input
                  className={`form-control ${errors.company_name || error?.company_name ? 'is-invalid' : ''}`}
                  placeholder="Mason Construction Pvt Ltd"
                  {...register('company_name', { required: 'Company name is required' })}
                />
                {errors.company_name && <div className="invalid-feedback">{errors.company_name.message}</div>}
                {fieldError('company_name')}
              </div>
              <div className="mb-3">
                <label className="form-label fw-semibold">Your Name</label>
                <input
                  className={`form-control ${errors.name || error?.name ? 'is-invalid' : ''}`}
                  placeholder="Admin User"
                  {...register('name', { required: 'Name is required' })}
                />
                {errors.name && <div className="invalid-feedback">{errors.name.message}</div>}
                {fieldError('name')}
              </div>
              <div className="mb-3">
                <label className="form-label fw-semibold">Email</label>
                <input
                  type="email"
                  className={`form-control ${errors.email || error?.email ? 'is-invalid' : ''}`}
                  placeholder="admin@company.com"
                  {...register('email', {
                    required: 'Email is required',
                    pattern: { value: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, message: 'Enter a valid email' },
                  })}
                />
                {errors.email && <div className="invalid-feedback">{errors.email.message}</div>}
                {fieldError('email')}
              </div>
              <div className="mb-3">
                <label className="form-label fw-semibold">Password</label>
                <input
                  type="password"
                  className={`form-control ${errors.password || error?.password ? 'is-invalid' : ''}`}
                  placeholder="Minimum 8 characters"
                  {...register('password', {
                    required: 'Password is required',
                    minLength: { value: 8, message: 'Minimum 8 characters' },
                    pattern: {
                      value: /^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/,
                      message: 'Need uppercase, number, and special character',
                    },
                  })}
                />
                {errors.password && <div className="invalid-feedback">{errors.password.message}</div>}
                {fieldError('password')}
              </div>
              <div className="mb-4">
                <label className="form-label fw-semibold">Confirm Password</label>
                <input
                  type="password"
                  className={`form-control ${errors.password_confirmation ? 'is-invalid' : ''}`}
                  placeholder="Repeat password"
                  {...register('password_confirmation', {
                    required: 'Please confirm your password',
                    validate: (v) => v === password || 'Passwords do not match',
                  })}
                />
                {errors.password_confirmation && (
                  <div className="invalid-feedback">{errors.password_confirmation.message}</div>
                )}
              </div>
              <button type="submit" className="btn btn-primary w-100" disabled={loading}>
                {loading ? (
                  <><span className="spinner-border spinner-border-sm me-2" />Creating account...</>
                ) : 'Create Account'}
              </button>
            </form>
            <hr />
            <p className="text-center text-muted small mb-0">
              Already have an account?{' '}
              <Link to="/login" className="text-decoration-none">Sign in</Link>
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
