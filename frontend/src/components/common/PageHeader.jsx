import React from 'react';

export default function PageHeader({ title, subtitle, action }) {
  return (
    <div className="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 className="mb-0 fw-bold">{title}</h4>
        {subtitle && <small className="text-muted">{subtitle}</small>}
      </div>
      {action && <div>{action}</div>}
    </div>
  );
}
