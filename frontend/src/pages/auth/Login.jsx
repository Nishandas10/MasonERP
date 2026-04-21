import React, { useEffect } from 'react';
import { useForm } from 'react-hook-form';
import { useDispatch, useSelector } from 'react-redux';
import { Link, useNavigate } from 'react-router-dom';
import { loginUser, clearError } from '../../store/slices/authSlice';
import { toast } from 'react-toastify';

const DEMOS = [
  { label: 'Admin', email: 'admin@mason.demo', password: 'Admin@1234' },
  { label: 'Project Manager', email: 'pm@mason.demo', password: 'Admin@1234' },
  { label: 'Site Engineer', email: 'engineer@mason.demo', password: 'Admin@1234' },
  { label: 'Accounts', email: 'accounts@mason.demo', password: 'Admin@1234' },
];

export default function Login() {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { loading, error, token } = useSelector((s) => s.auth);

  const { register, handleSubmit, setValue, formState: { errors } } = useForm();

  const fillDemo = (demo) => {
    setValue('email', demo.email);
    setValue('password', demo.password);
  };

  useEffect(() => {
    if (token) navigate('/dashboard', { replace: true });
    return () => dispatch(clearError());
  }, [token, navigate, dispatch]);

  const onSubmit = async (data) => {
    const res = await dispatch(loginUser(data));
    if (loginUser.fulfilled.match(res)) {
      toast.success('Welcome back!');
      navigate('/dashboard');
    }
  };

  return (
    <div className="min-vh-100 d-flex align-items-center justify-content-center bg-light">
      <div style={{ width: '100%', maxWidth: 420 }}>
        <div className="text-center mb-4">
          <div className="fs-1 mb-1">🏗️</div>
          <h3 className="fw-bold">Mason ERP</h3>
          <p className="text-muted">Sign in to your account</p>
        </div>
        <div className="card shadow-sm border-0">
          <div className="card-body p-4">
            <div className="alert alert-info py-2 mb-3 small">
              <div className="mb-1"><i className="bi bi-info-circle me-1" /><strong>Demo accounts</strong> — password: <span className="font-monospace">Admin@1234</span></div>
              <div className="d-flex flex-wrap gap-1 mt-2">
                {DEMOS.map((d) => (
                  <button key={d.email} type="button" className="btn btn-sm btn-outline-primary" onClick={() => fillDemo(d)}>
                    {d.label}
                  </button>
                ))}
              </div>
            </div>
            {error?.general && (
              <div className="alert alert-danger py-2">
                {Array.isArray(error.general) ? error.general[0] : error.general}
              </div>
            )}
            <form onSubmit={handleSubmit(onSubmit)}>
              <div className="mb-3">
                <label className="form-label fw-semibold">Email</label>
                <input
                  type="email"
                  className={`form-control ${errors.email ? 'is-invalid' : ''}`}
                  placeholder="admin@mason.demo"
                  {...register('email', {
                    required: 'Email is required',
                    pattern: { value: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, message: 'Enter a valid email' },
                  })}
                />
                {errors.email && <div className="invalid-feedback">{errors.email.message}</div>}
                {error?.email && <div className="invalid-feedback d-block">{error.email[0]}</div>}
              </div>
              <div className="mb-4">
                <label className="form-label fw-semibold">Password</label>
                <input
                  type="password"
                  className={`form-control ${errors.password ? 'is-invalid' : ''}`}
                  placeholder="Enter your password"
                  {...register('password', { required: 'Password is required' })}
                />
                {errors.password && <div className="invalid-feedback">{errors.password.message}</div>}
                {error?.password && <div className="invalid-feedback d-block">{error.password[0]}</div>}
              </div>
              <button
                type="submit"
                className="btn btn-primary w-100"
                disabled={loading}
              >
                {loading ? (
                  <><span className="spinner-border spinner-border-sm me-2" />Signing in...</>
                ) : 'Sign In'}
              </button>
            </form>
            <hr />
            <p className="text-center text-muted small mb-0">
              New company?{' '}
              <Link to="/register" className="text-decoration-none">Create account</Link>
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
