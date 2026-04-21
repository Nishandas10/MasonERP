import React from 'react';
import { NavLink, useNavigate } from 'react-router-dom';
import { useDispatch, useSelector } from 'react-redux';
import { logout } from '../../store/slices/authSlice';

const navItems = [
  { path: '/dashboard',      icon: 'bi-speedometer2',   label: 'Dashboard' },
  { path: '/projects',       icon: 'bi-building',       label: 'Projects' },
  { path: '/materials',      icon: 'bi-box-seam',       label: 'Materials' },
  { path: '/vendors',        icon: 'bi-shop',           label: 'Vendors' },
  { path: '/purchase-orders',icon: 'bi-receipt-cutoff', label: 'Purchase Orders' },
  { path: '/indents',        icon: 'bi-clipboard-check',label: 'Indents' },
  { path: '/laborers',       icon: 'bi-people-fill',    label: 'Labor' },
  { path: '/subcontractors', icon: 'bi-person-badge',   label: 'Subcontractors' },
  { path: '/equipment',      icon: 'bi-truck',          label: 'Equipment' },
  { path: '/expenses',       icon: 'bi-cash-stack',     label: 'Expenses' },
  { path: '/users',          icon: 'bi-person-gear',    label: 'Users' },
];

export default function Sidebar() {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const user = useSelector((state) => state.auth.user);

  const handleLogout = () => {
    dispatch(logout());
    navigate('/login');
  };

  return (
    <div
      className="d-flex flex-column bg-dark text-white vh-100 position-fixed"
      style={{ width: '240px', zIndex: 100 }}
    >
      {/* Brand */}
      <div className="p-3 border-bottom border-secondary">
        <div className="d-flex align-items-center gap-2">
          <span className="fs-4">🏗️</span>
          <div>
            <div className="fw-bold fs-6">Mason ERP</div>
            <small className="text-muted" style={{ fontSize: '11px' }}>
              {user?.company?.name || 'Construction ERP'}
            </small>
          </div>
        </div>
      </div>

      {/* Nav */}
      <nav className="flex-grow-1 overflow-auto py-2">
        {navItems.map((item) => (
          <NavLink
            key={item.path}
            to={item.path}
            className={({ isActive }) =>
              `d-flex align-items-center gap-2 px-3 py-2 text-decoration-none small
               ${isActive ? 'bg-primary text-white' : 'text-white-50 hover-text-white'}`
            }
            style={{ transition: 'all 0.15s' }}
          >
            <i className={`bi ${item.icon}`} />
            {item.label}
          </NavLink>
        ))}
      </nav>

      {/* User */}
      <div className="p-3 border-top border-secondary">
        <div className="d-flex align-items-center gap-2 mb-2">
          <div className="rounded-circle bg-primary d-flex align-items-center justify-content-center"
               style={{ width: 32, height: 32, fontSize: 14 }}>
            {user?.name?.charAt(0)?.toUpperCase()}
          </div>
          <div style={{ lineHeight: 1.2 }}>
            <div className="small fw-semibold">{user?.name}</div>
            <div className="text-muted" style={{ fontSize: 11 }}>{user?.role?.name}</div>
          </div>
        </div>
        <button
          className="btn btn-outline-danger btn-sm w-100"
          onClick={handleLogout}
        >
          <i className="bi bi-box-arrow-right me-1" />
          Logout
        </button>
      </div>
    </div>
  );
}
