import api from '@/Services/api';

export const fetchCart = () =>
    api.get('/cart-data');

export const addToCart = (productId, quantity = 1) =>
    api.post('/cart-data/items', {
        product_id: productId,
        quantity,
    });

export const updateCartItem = (productId, quantity) =>
    api.patch(`/cart-data/items/${productId}`, {
        quantity,
    });

export const removeCartItem = (productId) =>
    api.delete(`/cart-data/items/${productId}`);

export const checkout = () =>
    api.post('/cart/checkout');
