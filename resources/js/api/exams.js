import { request } from './client.js';

export const configure = (body) => request.post('/exams/configure', body);

export const getQuestions = (attemptId) => request.get(`/exams/${attemptId}/questions`);

export const submitAnswer = (attemptId, body) => request.post(`/exams/${attemptId}/answer`, body);

export const finish = (attemptId, body = {}) => request.post(`/exams/${attemptId}/finish`, body);

export const results = (attemptId) => request.get(`/exams/${attemptId}/results`);
