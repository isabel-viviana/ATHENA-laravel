import { request } from './client.js';

export const dashboard = () => request.get('/progress');

export const history = () => request.get('/progress/history');

export const stats = () => request.get('/progress/stats');

export const performanceBySubject = (subjectId) => request.get(`/progress/subject/${subjectId}`);

export const performanceByTopic = (topicId) => request.get(`/progress/topic/${topicId}`);
