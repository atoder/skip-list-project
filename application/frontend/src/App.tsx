import React, { useState, useEffect, FormEvent } from 'react';

// Import animation libraries
import { motion, AnimatePresence } from 'framer-motion';

// Import HTTP client
import axios from 'axios';

// TypeScript Interfaces:
// It must match the JSON structure from the PHP API.
export interface SkipListNode {
  value: number | string;
  // 0-indexed height
  height: number;
  // Unique ID for React's 'key' prop
  id: string;
}

export interface SkipListStructure {
  total_height: number;
  node_count: number;
  type: 'integer' | 'string' | null;
  nodes: SkipListNode[];
}

// API Configuration
// It reads from the .env file, with a fallback to the hard-coded URL for safety.
const API_BASE_URL = process.env.REACT_APP_API_BASE_URL || 'http://localhost:8000/index.php';

// 🔐 DEVELOPMENT-ONLY: Enable cross-origin cookies for PHP session persistence
// Required because browser blocks cookies between localhost:3000 (frontend) and localhost:8000 (backend)
// In production (same domain), cookies work automatically - this setting is unnecessary
if (process.env.NODE_ENV === 'development') {
  axios.defaults.withCredentials = true;
}

// Dark Mode Hook
function useDarkMode() {
  const [theme, setTheme] = useState(() => {
    if (typeof window !== 'undefined' && window.localStorage) {
      const savedTheme = localStorage.getItem('theme');
      if (savedTheme) {
        return savedTheme;
      }
      if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        return 'dark';
      }
    }
    return 'light';
  });

  useEffect(() => {
    if (typeof window !== 'undefined' && window.document) {
      const root = window.document.documentElement;
      if (theme === 'dark') {
        root.classList.add('dark');
      } else {
        root.classList.remove('dark');
      }
      localStorage.setItem('theme', theme);
    }
  }, [theme]);

  const toggleTheme = () => {
    setTheme(theme === 'dark' ? 'light' : 'dark');
  };

  // Tuple (a locked-shape, readonly array).
  return [theme, toggleTheme] as const;
}

// React Component Start.

// Height of one single level.
const CELL_HEIGHT_PX = 60;

/**
 * The main application component for the Skip List Visualizer.
 */
function App() {
  // State
  const [listData, setListData] = useState<SkipListStructure | null>(null);
  const [inputValue, setInputValue] = useState('');
  const [message, setMessage] = useState('');
  const [isLoading, setIsLoading] = useState(true);
  const [theme, toggleTheme] = useDarkMode();

  const fetchList = async () => {
    setIsLoading(true);
    try {
      // Use Axios to call the PHP API.
      // Added a timestamp to bust the browser cache.
      const response = await axios.get(`${API_BASE_URL}?action=getall&t=${new Date().getTime()}`);

      if (response.data.success) {
        // The PHP API doesn't send a unique ID, but React needs one
        // for animations. We add it here on the frontend.
        const nodesWithId = response.data.structure.nodes.map((node: any) => ({
          ...node,
          id: crypto.randomUUID() // Add a unique ID for Framer Motion
        }));

        setListData({ ...response.data.structure, nodes: nodesWithId });
      } else {
        // Handle errors from the API (ex: type mismatch)
        setMessage(`API Error: ${response.data.message || 'Unknown error'}`);
      }
    } catch (error: any) {
      // Handle network-level errors (ex: API server is down)
      setMessage(`Fetch Error: ${error.message || 'Could not connect to API'}. Is the PHP server running?`);
    } finally {
      setIsLoading(false);
    }
  };

  // Load the list on first render
  useEffect(() => {
    fetchList();
  }, []);

  const handleSubmit = async (e: FormEvent, action: 'insert' | 'delete') => {
    e.preventDefault();
    if (!inputValue) return;
    setMessage('');

    try {
      // Use Axios to call the REAL PHP API
      const response = await axios.post(`${API_BASE_URL}?action=${action}`, {
        value: inputValue,
      });

      setMessage(response.data.message);

      // If the API call was a success, refresh the list
      if (response.data.success) {
        setInputValue('');
        fetchList();
      }
    } catch (error: any) {
      setMessage(`Submit Error: ${error.message}`);
    }
  };

  const handleReset = async () => {
  // Clear old messages
    setMessage('');
    try {
      // Use Axios to call PHP API
      const response = await axios.post(`${API_BASE_URL}?action=reset`, { value: '' });
      setMessage(response.data.message);
      if (response.data.success) {
        // Clear input field
        setInputValue('');
        // Refresh the list (will now be empty)
        fetchList();
      }
    } catch (error: any) {
      setMessage(`Reset Error: ${error.message}`);
    }
  };

  // Helper Functions

  // Create an array of level numbers for rendering, ex: [0, 1, 2]
  const allLevels = listData ? Array.from({ length: listData.total_height + 1 }, (_, i) => i) : [0];

  const getContainerHeight = (): string => {
    // numLevels: This gets the total number of levels (ex: height=3 means 4 levels: 0, 1, 2, 3).
    const numLevels = (listData?.total_height ?? 0) + 1;

    // (numLevels * CELL_HEIGHT_PX): Calculates the space needed for the levels (ex: 4 * 60px).
    // (+ CELL_HEIGHT_PX): Adds one final 60px buffer at the bottom for the value labels.
    return `${(numLevels * CELL_HEIGHT_PX) + CELL_HEIGHT_PX}px`;
  };

  // Render

  return (
    // Main container
    <div className="max-w-full mx-auto p-4 sm:p-8 font-sans bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen transition-colors duration-300">

      {/* Header & Theme Toggle */}
      <header className="mb-8 flex justify-between items-center">
        <div>
          <h1 className="text-4xl font-bold text-gray-800 dark:text-white">Skip List Visualizer</h1>
          <p className="text-lg text-gray-600 dark:text-gray-400">
            A React + PHP implementation of a SortedLinkedList.
          </p>
        </div>
        <button
          onClick={toggleTheme}
          className="p-3 rounded-full bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
          aria-label="Toggle dark mode"
        >
          {theme === 'light' ? (
            <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
          ) : (
            <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6 text-yellow-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          )}
        </button>
      </header>

      {/* Controls */}
      <div className="mb-8 pb-6 border-b border-gray-200 dark:border-gray-700">
        <form
          onSubmit={(e) => handleSubmit(e, 'insert')}
          className="flex flex-col sm:flex-row items-center gap-4"
        >
          <input
            type="text"
            value={inputValue}
            onChange={(e) => setInputValue(e.target.value)}
            placeholder="Enter int or string"
            className="flex-grow w-full sm:w-auto text-lg p-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm
                       bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                       focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
          />
          <button
            type="submit"
            className="w-full sm:w-auto text-lg px-6 py-3 rounded-lg text-white font-semibold bg-blue-600
                       hover:bg-blue-700 shadow-md transition-all duration-200 transform hover:-translate-y-0.5"
          >
            Insert
          </button>
          <button
            type="button"
            onClick={(e) => handleSubmit(e as any, 'delete')}
            className="w-full sm:w-auto text-lg px-6 py-3 rounded-lg text-white font-semibold bg-red-600
                       hover:bg-red-700 shadow-md transition-all duration-200 transform hover:-translate-y-0.5"
          >
            Delete
          </button>
          <button
            type="button"
            onClick={handleReset}
            className="w-full sm:w-auto text-lg px-6 py-3 rounded-lg text-white font-semibold
                       bg-gray-500 hover:bg-gray-600 dark:bg-gray-600 dark:hover:bg-gray-500
                       shadow-md transition-all duration-200 transform hover:-translate-y-0.5"
          >
            Reset
          </button>
        </form>
        {message && (
          <p className="mt-4 text-gray-700 dark:text-gray-300 italic bg-gray-100 dark:bg-gray-800 p-3 rounded-lg">
            {message}
          </p>
        )}
      </div>

      {/* Visualization Area */}
      <div className="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
        <h3 className="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4">
          List (Type:
          <span className="font-mono text-blue-600 dark:text-blue-400 capitalize"> {listData?.type || 'N/A'}</span>
          , Max Height:
          <span className="font-mono text-blue-600 dark:text-blue-400"> {listData?.total_height ?? 0}</span>
          , Count:
          <span className="font-mono text-blue-600 dark:text-blue-400"> {listData?.node_count ?? 0}</span>
          )
        </h3>

        {isLoading ? (
          <div className="flex justify-center items-center h-80">
            <p className="text-lg text-gray-500 dark:text-gray-400">Loading List...</p>
          </div>
        ) : (
          <div
            className="flex items-start pt-5 overflow-x-auto overflow-y-hidden"
            style={{ height: getContainerHeight() }}
          >
            {/* Level Labels Column */}
            <div className={`flex flex-col-reverse min-w-[70px] mx-1.5`} style={{paddingBottom: `${CELL_HEIGHT_PX}px`}}>
              {allLevels.map(level => (
                <div
                  key={`label-${level}`}
                  style={{ height: `${CELL_HEIGHT_PX}px` }}
                  className="flex items-center justify-center font-bold text-gray-500 dark:text-gray-400"
                >
                  L{level}
                </div>
              ))}
            </div>

            {/* Header Column */}
            <div className="flex flex-col-reverse min-w-[80px] mx-1.5" title="Header Node">
              {allLevels.map(level => (
                <div
                  key={`header-${level}`}
                  className="flex justify-center items-center relative"
                  style={{ height: `${CELL_HEIGHT_PX}px` }}
                >
                  {/* Vertical connecting line */}
                  {level > 0 && (
                    <div className="absolute top-0 left-1/2 w-0.5 h-full bg-gray-400 dark:bg-gray-600 -translate-x-1/2" />
                  )}
                  <div className="h-12 w-full rounded border-2 border-gray-600 dark:border-gray-400 bg-gray-200 dark:bg-gray-600 z-10" />
                </div>
              ))}
              <div
                style={{ height: `${CELL_HEIGHT_PX}px` }}
                className="flex items-center justify-center font-bold text-gray-800 dark:text-gray-100"
              >
                [Head]
              </div>
            </div>

            {/* Data Nodes (Animated with Framer Motion) */}
            {React.createElement(AnimatePresence as any, { mode: "popLayout" },
              listData?.nodes.map((node) => (
                <motion.div
                  key={node.id}
                  layout
                  initial={{ opacity: 0, scale: 0.8 }}
                  animate={{ opacity: 1, scale: 1 }}
                  exit={{ opacity: 0, scale: 0.8 }}
                  transition={{ duration: 0.4, ease: "easeInOut" }}
                  className="flex flex-col-reverse min-w-[80px] mx-1.5"
                  title={`Value: ${node.value} | Height: ${node.height}`}
                >
                  {allLevels.map(level => (
                    <div
                      key={level}
                      className="flex justify-center items-center relative"
                      style={{ height: `${CELL_HEIGHT_PX}px` }}
                    >
                      {node.height >= level ? (
                        <>
                          {/* Vertical connecting line */}
                          {level > 0 && (
                            <div className="absolute top-0 left-1/2 w-0.5 h-full bg-blue-300 dark:bg-blue-700 -translate-x-1/2" />
                          )}
                          {/* Node Cell */}
                          <div className="h-12 w-full rounded border-2 border-blue-500 dark:border-blue-400
                            bg-blue-100 dark:bg-blue-800 z-10"
                          />
                        </>
                      ) : (
                        // Placeholder Cell (transparent)
                        <div className="h-12 w-full" />
                      )}
                    </div>
                  ))}
                  {/* Value at the bottom */}
                  <div
                    style={{ height: `${CELL_HEIGHT_PX}px` }}
                    className="flex items-center justify-center font-bold text-xl text-blue-800 dark:text-blue-100"
                  >
                    {String(node.value)}
                  </div>
                </motion.div>
              ))
            )}
          </div>
        )}
      </div>
    </div>
  );
}

export default App;
