import React from 'react';
import ReactDOM from 'react-dom/client';
import './index.css';
import App from './App';

// Find the root element where the React application will mount.
const container = document.getElementById('root');

// Ensure the container exists before attempting to mount.
if (container) {
    // Create a React root and render the main App component.
    const root = ReactDOM.createRoot(container);
    root.render(
        <React.StrictMode>
            <App />
        </React.StrictMode>
    );
} else {
    console.error("Failed to find the root element to mount the React application.");
}
