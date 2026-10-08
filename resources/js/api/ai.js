import { request } from './client.js';

export const sendMessage = (body) => request.post('/ai/message', body);

export const history = () => request.get('/ai/history');

export const getConversation = (conversationId) => request.get(`/ai/conversations/${conversationId}`);
