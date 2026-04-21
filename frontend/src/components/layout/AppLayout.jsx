import React from 'react';
import Sidebar from './Sidebar';

export default function AppLayout({ children }) {
  return (
    <div className="d-flex">
      <Sidebar />
      <main
        className="flex-grow-1 bg-light min-vh-100"
        style={{ marginLeft: '240px' }}
      >
        <div className="p-4">{children}</div>
      </main>
    </div>
  );
}
