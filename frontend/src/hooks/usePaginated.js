import { useState, useEffect, useCallback } from 'react';
import { toast } from 'react-toastify';

export function usePaginated(apiFn, defaultFilters = {}) {
  const [data, setData] = useState([]);
  const [meta, setMeta] = useState(null);
  const [loading, setLoading] = useState(false);
  const [filters, setFilters] = useState(defaultFilters);
  const [page, setPage] = useState(1);

  const fetch = useCallback(async (newFilters, newPage = 1) => {
    setLoading(true);
    try {
      const params = { ...newFilters, page: newPage };
      const res = await apiFn(params);
      const { data: items, meta: pageMeta } = res.data;
      setData(items?.data || items || []);
      setMeta(pageMeta || items?.meta || null);
    } catch (err) {
      toast.error(err.response?.data?.message || 'Failed to load data.');
    } finally {
      setLoading(false);
    }
  }, [apiFn]);

  useEffect(() => {
    fetch(filters, page);
  }, [fetch, filters, page]);

  const refresh = () => fetch(filters, page);
  const applyFilters = (newFilters) => { setFilters(newFilters); setPage(1); };

  return { data, meta, loading, page, setPage, refresh, applyFilters, filters };
}
