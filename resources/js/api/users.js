import { request } from './client.js';

export const list = () => request.get('/users');

export const create = (body) => request.post('/users', body);

export const show = (userId) => request.get(`/users/${userId}`);

export const update = (userId, body) => request.put(`/users/${userId}`, body);

export const destroy = (userId) => request.delete(`/users/${userId}`);

export const changeRole = (userId, body) => request.patch(`/users/${userId}/role`, body);

export const toggleStatus = (userId, body) => request.patch(`/users/${userId}/status`, body);
