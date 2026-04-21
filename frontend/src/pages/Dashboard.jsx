import React, { useEffect, useState } from 'react';
import { projectApi } from '../api/endpoints';
import PageHeader from '../components/common/PageHeader';
import StatusBadge from '../components/common/StatusBadge';

function StatCard({ icon, label, value, color, sub }) {
  return (
    <div className="col-md-6 col-xl-3">
      <div className={`card border-0 border-start border-4 border-${color} shadow-sm`}>
        <div className="card-body">
          <div className="d-flex align-items-center justify-content-between">
            <div>
              <div className="text-muted small mb-1">{label}</div>
              <div className="fs-3 fw-bold">{value ?? '—'}</div>
              {sub && <div className="text-muted small">{sub}</div>}
            </div>
            <div className={`text-${color} fs-2 opacity-50`}>{icon}</div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default function Dashboard() {
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    projectApi.dashboard()
      .then((res) => setStats(res.data.data))
      .catch(() => {})
      .finally(() => setLoading(false));
  }, []);

  const fmt = (n) => (n ? Number(n).toLocaleString('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }) : '₹0');

  if (loading) {
    return (
      <div className="text-center py-5">
        <div className="spinner-border text-primary" />
      </div>
    );
  }

  const projects = stats?.projects || {};
  const finance = stats?.finance || {};
  const labor = stats?.labor || {};
  const equipment = stats?.equipment || {};
  const procurement = stats?.procurement || {};
  const recentProjects = stats?.recent_projects || [];

  return (
    <div>
      <PageHeader title="Dashboard" subtitle="Overview of your construction operations" />

      <div className="row g-3 mb-4">
        <StatCard icon="🏗️" label="Total Projects" value={projects.total} color="primary" sub={`${projects.in_progress || 0} in progress`} />
        <StatCard icon="💰" label="Total Budget" value={fmt(projects.total_budget)} color="success" />
        <StatCard icon="📋" label="Pending Indents" value={procurement.pending_indents} color="warning" />
        <StatCard icon="👷" label="Active Laborers" value={labor.active} color="info" />
      </div>

      <div className="row g-3 mb-4">
        <StatCard icon="✅" label="Completed Projects" value={projects.completed} color="success" />
        <StatCard icon="💸" label="Total Expenses" value={fmt(finance.total_expenses)} color="danger" />
        <StatCard icon="🚛" label="Equipment Available" value={equipment.available} color="primary" sub={`${equipment.deployed || 0} deployed`} />
        <StatCard icon="📌" label="On Hold" value={projects.on_hold} color="secondary" />
      </div>

      {/* Recent Projects */}
      <div className="card border-0 shadow-sm">
        <div className="card-header bg-white fw-bold">Recent Projects</div>
        <div className="table-responsive">
          <table className="table table-hover mb-0 align-middle">
            <thead className="table-light">
              <tr>
                <th>Project</th>
                <th>Client</th>
                <th>Status</th>
                <th>Budget</th>
                <th>Progress</th>
              </tr>
            </thead>
            <tbody>
              {recentProjects.length === 0 ? (
                <tr><td colSpan={5} className="text-center text-muted py-3">No projects yet</td></tr>
              ) : recentProjects.map((p) => (
                <tr key={p.id}>
                  <td className="fw-semibold">{p.name}</td>
                  <td className="text-muted">{p.client_name || '—'}</td>
                  <td><StatusBadge status={p.status} /></td>
                  <td>{fmt(p.budget)}</td>
                  <td>
                    <div className="d-flex align-items-center gap-2">
                      <div className="progress flex-grow-1" style={{ height: 8 }}>
                        <div
                          className="progress-bar bg-success"
                          style={{ width: `${p.progress_percent || 0}%` }}
                        />
                      </div>
                      <small className="text-muted">{p.progress_percent || 0}%</small>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
