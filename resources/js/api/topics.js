import { request } from './client.js';

export const list = () => request.get('/topics');

export const create = (body) => request.post('/topics', body);

export const show = (topicId) => request.get(`/topics/${topicId}`);

export const update = (topicId, body) => request.put(`/topics/${topicId}`, body);

export const destroy = (topicId) => request.delete(`/topics/${topicId}`);
