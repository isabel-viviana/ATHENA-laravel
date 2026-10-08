import { request } from './client.js';

export const list = () => request.get('/subjects');

export const create = (body) => request.post('/subjects', body);

export const show = (subjectId) => request.get(`/subjects/${subjectId}`);

export const update = (subjectId, body) => request.put(`/subjects/${subjectId}`, body);

export const destroy = (subjectId) => request.delete(`/subjects/${subjectId}`);
