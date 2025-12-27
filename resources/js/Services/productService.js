import api from './api';

export const fetchProducts = (page = 1, limit = 20) =>
    api.get("/api/products", {
        params: { page, limit },
    });
