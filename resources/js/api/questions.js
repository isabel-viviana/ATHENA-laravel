import { request } from './client.js';

export const list = () => request.get('/questions');

export const create = (body) => request.post('/questions', body);

export const show = (questionId) => request.get(`/questions/${questionId}`);

export const update = (questionId, body) => request.put(`/questions/${questionId}`, body);

export const destroy = (questionId) => request.delete(`/questions/${questionId}`);
