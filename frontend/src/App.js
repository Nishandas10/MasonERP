import React, { Suspense } from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { Provider } from 'react-redux';
import { ToastContainer } from 'react-toastify';
import { store } from './store';

import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import 'react-toastify/dist/ReactToastify.css';
import './App.css';

import ProtectedRoute from './components/auth/ProtectedRoute';
import Login from './pages/auth/Login';
import Register from './pages/auth/Register';
import Dashboard from './pages/Dashboard';

import ProjectList from './pages/projects/ProjectList';
import ProjectForm from './pages/projects/ProjectForm';
import ProjectDetail from './pages/projects/ProjectDetail';

import IndentList from './pages/indents/IndentList';
import IndentForm from './pages/indents/IndentForm';
import IndentDetail from './pages/indents/IndentDetail';

import SubcontractorList from './pages/subcontractors/SubcontractorList';
import SubcontractorForm from './pages/subcontractors/SubcontractorForm';
import SubcontractorDetail from './pages/subcontractors/SubcontractorDetail';

import VendorList from './pages/vendors/VendorList';
import VendorDetail from './pages/vendors/VendorDetail';
import VendorForm from './pages/vendors/VendorForm';
import PurchaseOrderList from './pages/purchaseorders/PurchaseOrderList';
import PurchaseOrderForm from './pages/purchaseorders/PurchaseOrderForm';
import MaterialList from './pages/materials/MaterialList';
import EquipmentList from './pages/equipment/EquipmentList';
import LaborList from './pages/laborers/LaborList';
import ExpenseList from './pages/expenses/ExpenseList';
import UserList from './pages/users/UserList';

function Loading() {
  return (
    <div className="d-flex align-items-center justify-content-center min-vh-100">
      <div className="spinner-border text-primary" />
    </div>
  );
}

function App() {
  return (
    <Provider store={store}>
      <BrowserRouter>
        <Suspense fallback={<Loading />}>
          <Routes>
            <Route path="/login" element={<Login />} />
            <Route path="/register" element={<Register />} />

            <Route path="/dashboard" element={<ProtectedRoute><Dashboard /></ProtectedRoute>} />

            <Route path="/projects" element={<ProtectedRoute><ProjectList /></ProtectedRoute>} />
            <Route path="/projects/new" element={<ProtectedRoute><ProjectForm /></ProtectedRoute>} />
            <Route path="/projects/:id" element={<ProtectedRoute><ProjectDetail /></ProtectedRoute>} />
            <Route path="/projects/:id/edit" element={<ProtectedRoute><ProjectForm /></ProtectedRoute>} />

            <Route path="/indents" element={<ProtectedRoute><IndentList /></ProtectedRoute>} />
            <Route path="/indents/new" element={<ProtectedRoute><IndentForm /></ProtectedRoute>} />
            <Route path="/indents/:id" element={<ProtectedRoute><IndentDetail /></ProtectedRoute>} />
            <Route path="/indents/:id/edit" element={<ProtectedRoute><IndentForm /></ProtectedRoute>} />

            <Route path="/subcontractors" element={<ProtectedRoute><SubcontractorList /></ProtectedRoute>} />
            <Route path="/subcontractors/new" element={<ProtectedRoute><SubcontractorForm /></ProtectedRoute>} />
            <Route path="/subcontractors/:id" element={<ProtectedRoute><SubcontractorDetail /></ProtectedRoute>} />
            <Route path="/subcontractors/:id/edit" element={<ProtectedRoute><SubcontractorForm /></ProtectedRoute>} />

            <Route path="/vendors" element={<ProtectedRoute><VendorList /></ProtectedRoute>} />
            <Route path="/vendors/new" element={<ProtectedRoute><VendorForm /></ProtectedRoute>} />
            <Route path="/vendors/:id" element={<ProtectedRoute><VendorDetail /></ProtectedRoute>} />
            <Route path="/vendors/:id/edit" element={<ProtectedRoute><VendorForm /></ProtectedRoute>} />

            <Route path="/purchase-orders" element={<ProtectedRoute><PurchaseOrderList /></ProtectedRoute>} />
            <Route path="/purchase-orders/new" element={<ProtectedRoute><PurchaseOrderForm /></ProtectedRoute>} />
            <Route path="/purchase-orders/:id/edit" element={<ProtectedRoute><PurchaseOrderForm /></ProtectedRoute>} />

            <Route path="/materials" element={<ProtectedRoute><MaterialList /></ProtectedRoute>} />
            <Route path="/equipment" element={<ProtectedRoute><EquipmentList /></ProtectedRoute>} />
            <Route path="/laborers" element={<ProtectedRoute><LaborList /></ProtectedRoute>} />
            <Route path="/expenses" element={<ProtectedRoute><ExpenseList /></ProtectedRoute>} />
            <Route path="/users" element={<ProtectedRoute><UserList /></ProtectedRoute>} />

            <Route path="/" element={<Navigate to="/dashboard" replace />} />
            <Route path="*" element={<Navigate to="/dashboard" replace />} />
          </Routes>
        </Suspense>
        <ToastContainer
          position="top-right"
          autoClose={3000}
          hideProgressBar={false}
          newestOnTop
          closeOnClick
          pauseOnHover
          theme="light"
        />
      </BrowserRouter>
    </Provider>
  );
}

export default App;
