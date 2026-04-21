import React from 'react';

const statusColors = {
  active: 'success', inactive: 'secondary', planned: 'info',
  in_progress: 'primary', on_hold: 'warning', completed: 'success',
  cancelled: 'danger', draft: 'secondary', submitted: 'info',
  approved: 'success', rejected: 'danger', pending: 'warning',
  paid: 'success', partially_paid: 'info', blacklisted: 'danger',
  deployed: 'primary', available: 'success', maintenance: 'warning',
  breakdown: 'danger', retired: 'secondary',
};

const statusLabels = {
  in_progress: 'In Progress', on_hold: 'On Hold', partially_paid: 'Part. Paid',
};

export default function StatusBadge({ status }) {
  const color = statusColors[status] || 'secondary';
  const label = statusLabels[status] || (status ? status.charAt(0).toUpperCase() + status.slice(1) : '—');
  return <span className={`badge bg-${color}`}>{label}</span>;
}
