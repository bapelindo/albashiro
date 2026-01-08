/**
 * Database Connection Module
 * Supports both localhost and Vercel (TiDB)
 */

import mysql from 'mysql2/promise';

// Environment detection
const isVercel = process.env.VERCEL === '1';

// Database configuration
const dbConfig = {
    host: process.env.TIDB_HOST || 'gateway01.ap-northeast-1.prod.aws.tidbcloud.com',
    port: parseInt(process.env.TIDB_PORT || '4000'),
    user: process.env.TIDB_USER || '4TnpUUxik5ZLHTT.root',
    password: process.env.TIDB_PASSWORD || 'hweuQGiW36RtoJLw',
    database: process.env.TIDB_DATABASE || 'albashiro',
    charset: 'utf8mb4',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0,
    enableKeepAlive: true,
    keepAliveInitialDelay: 0,
    // TiDB requires SSL
    ssl: {
        rejectUnauthorized: true
    }
};

// Create connection pool
const pool = mysql.createPool(dbConfig);

// Test connection on startup
pool.getConnection()
    .then(connection => {
        console.log('✅ Database connected successfully');
        connection.release();
    })
    .catch(err => {
        console.error('❌ Database connection failed:', err.message);
    });

export default pool;
