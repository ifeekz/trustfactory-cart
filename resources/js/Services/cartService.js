import api from '@/Services/api';

export const fetchCart = () =>
    api.get('/cart');

export const addToCart = (productId, quantity = 1) =>
    api.post('/cart/items', {
        product_id: productId,
        quantity,
    });

export const updateCartItem = (productId, quantity) =>
    api.patch(`/cart/items/${productId}`, {
        quantity,
    });

export const removeCartItem = (productId) =>
    api.delete(`/cart/items/${productId}`);

export const checkout = () =>
    api.post('/cart/checkout');
