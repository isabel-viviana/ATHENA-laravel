import { request } from './client.js';

export const show = () => request.get('/profile');

export const update = (body) => request.put('/profile', body);
