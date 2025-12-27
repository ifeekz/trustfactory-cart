import api from './api';

export const fetchCart = () =>
    api.get('/api/cart');

export const addToCart = (productId, quantity = 1) =>
    api.post('/api/cart/items', {
        product_id: productId,
        quantity,
    });

export const updateCartItem = (productId, quantity) =>
    api.patch(`/api/cart/items/${productId}`, {
        quantity,
    });

export const removeCartItem = (productId) =>
    api.delete(`/api/cart/items/${productId}`);

export const checkout = () =>
    api.post('/api/cart/checkout');
