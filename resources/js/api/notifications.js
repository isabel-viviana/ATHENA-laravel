import { request } from './client.js';

export const list = () => request.get('/notifications');

export const markAsRead = (notificationId) => request.patch(`/notifications/${notificationId}/read`);
