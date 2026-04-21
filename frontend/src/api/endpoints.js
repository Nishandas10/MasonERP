import api from './axios';

export const authApi = {
  login: (data) => api.post('/auth/login', data),
  register: (data) => api.post('/auth/register', data),
  me: () => api.get('/auth/me'),
  logout: () => api.post('/auth/logout'),
  changePassword: (data) => api.post('/auth/change-password', data),
};

export const projectApi = {
  list: (params) => api.get('/projects', { params }),
  create: (data) => api.post('/projects', data),
  get: (id) => api.get(`/projects/${id}`),
  update: (id, data) => api.put(`/projects/${id}`, data),
  delete: (id) => api.delete(`/projects/${id}`),
  assignMember: (id, data) => api.post(`/projects/${id}/members`, data),
  removeMember: (id, userId) => api.delete(`/projects/${id}/members/${userId}`),
  dashboard: () => api.get('/dashboard'),
  milestones: (id) => api.get(`/projects/${id}/milestones`),
  createMilestone: (id, data) => api.post(`/projects/${id}/milestones`, data),
  boq: (id) => api.get(`/projects/${id}/boq`),
  createBoq: (id, data) => api.post(`/projects/${id}/boq`, data),
};

export const indentApi = {
  list: (params) => api.get('/indents', { params }),
  create: (data) => api.post('/indents', data),
  update: (id, data) => api.put(`/indents/${id}`, data),
  get: (id) => api.get(`/indents/${id}`),
  submit: (id) => api.post(`/indents/${id}/submit`),
  approve: (id) => api.post(`/indents/${id}/approve`),
  reject: (id, data) => api.post(`/indents/${id}/reject`, data),
  delete: (id) => api.delete(`/indents/${id}`),
};

export const vendorApi = {
  list: (params) => api.get('/vendors', { params }),
  create: (data) => api.post('/vendors', data),
  get: (id) => api.get(`/vendors/${id}`),
  update: (id, data) => api.put(`/vendors/${id}`, data),
  delete: (id) => api.delete(`/vendors/${id}`),
};

export const purchaseOrderApi = {
  list: (params) => api.get('/purchase-orders', { params }),
  create: (data) => api.post('/purchase-orders', data),
  get: (id) => api.get(`/purchase-orders/${id}`),
  update: (id, data) => api.put(`/purchase-orders/${id}`, data),
  delete: (id) => api.delete(`/purchase-orders/${id}`),
};

export const materialApi = {
  list: (params) => api.get('/materials', { params }),
  create: (data) => api.post('/materials', data),
  get: (id) => api.get(`/materials/${id}`),
  update: (id, data) => api.put(`/materials/${id}`, data),
  delete: (id) => api.delete(`/materials/${id}`),
};

export const subcontractorApi = {
  list: (params) => api.get('/subcontractors', { params }),
  create: (data) => api.post('/subcontractors', data),
  get: (id) => api.get(`/subcontractors/${id}`),
  update: (id, data) => api.put(`/subcontractors/${id}`, data),
  delete: (id) => api.delete(`/subcontractors/${id}`),
  contracts: (id, params) => api.get(`/subcontractors/${id}/contracts`, { params }),
  createContract: (id, data) => api.post(`/subcontractors/${id}/contracts`, data),
  bills: (id, params) => api.get(`/subcontractors/${id}/bills`, { params }),
  createBill: (contractId, data) => api.post(`/subcontractor-contracts/${contractId}/bills`, data),
  approveBill: (billId) => api.post(`/subcontractor-bills/${billId}/approve`),
  recordPayment: (billId, data) => api.post(`/subcontractor-bills/${billId}/payment`, data),
};

export const laborApi = {
  list: (params) => api.get('/laborers', { params }),
  create: (data) => api.post('/laborers', data),
  get: (id) => api.get(`/laborers/${id}`),
  update: (id, data) => api.put(`/laborers/${id}`, data),
  markAttendance: (data) => api.post('/attendance', data),
  getAttendance: (params) => api.get('/attendance', { params }),
};

export const equipmentApi = {
  list: (params) => api.get('/equipment', { params }),
  create: (data) => api.post('/equipment', data),
  get: (id) => api.get(`/equipment/${id}`),
  update: (id, data) => api.put(`/equipment/${id}`, data),
  assign: (id, data) => api.post(`/equipment/${id}/assign`, data),
  release: (id) => api.post(`/equipment/${id}/release`),
  addMaintenance: (id, data) => api.post(`/equipment/${id}/maintenance`, data),
};

export const expenseApi = {
  categories: () => api.get('/expense-categories'),
  createCategory: (data) => api.post('/expense-categories', data),
  list: (params) => api.get('/expenses', { params }),
  create: (data) => api.post('/expenses', data),
  approve: (id) => api.post(`/expenses/${id}/approve`),
  delete: (id) => api.delete(`/expenses/${id}`),
};

export const userApi = {
  list: (params) => api.get('/users', { params }),
  create: (data) => api.post('/users', data),
  get: (id) => api.get(`/users/${id}`),
  update: (id, data) => api.put(`/users/${id}`, data),
  delete: (id) => api.delete(`/users/${id}`),
};
