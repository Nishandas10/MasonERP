import React from 'react';

export default function Pagination({ meta, onPageChange }) {
  if (!meta || meta.last_page <= 1) return null;

  const pages = [];
  for (let i = 1; i <= meta.last_page; i++) {
    pages.push(i);
  }

  return (
    <div className="d-flex justify-content-between align-items-center mt-3">
      <small className="text-muted">
        Showing {meta.from}–{meta.to} of {meta.total} entries
      </small>
      <nav>
        <ul className="pagination pagination-sm mb-0">
          <li className={`page-item ${meta.current_page === 1 ? 'disabled' : ''}`}>
            <button className="page-link" onClick={() => onPageChange(meta.current_page - 1)}>‹</button>
          </li>
          {pages.map((p) => (
            <li key={p} className={`page-item ${p === meta.current_page ? 'active' : ''}`}>
              <button className="page-link" onClick={() => onPageChange(p)}>{p}</button>
            </li>
          ))}
          <li className={`page-item ${meta.current_page === meta.last_page ? 'disabled' : ''}`}>
            <button className="page-link" onClick={() => onPageChange(meta.current_page + 1)}>›</button>
          </li>
        </ul>
      </nav>
    </div>
  );
}
