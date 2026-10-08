import { request } from './client.js';

export const integrity = () => request.get('/test-db/integrity');

export const academic = () => request.get('/test-db/academic');

export const simulators = () => request.get('/test-db/simulators');

export const commercialAi = () => request.get('/test-db/commercial-ai');

export const hybrid = () => request.get('/test-db/hybrid');

export const gemini = () => request.get('/test-db/gemini');
