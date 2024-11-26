import React, { createContext, useContext, useState, useEffect } from 'react';
import axios from 'axios';

const NotificationsContext = createContext();

export const NotificationsProvider = ({ children }) => {
    const [notifications, setNotifications] = useState([]);
    const [unreadCount, setUnreadCount] = useState(0);

    // Function to fetch unread notifications count from the backend
    const fetchUnreadCount = async () => {
        try {
            const response = await axios.get('/notifications/unread-count');
            setUnreadCount(response.data.unread_count);
        } catch (error) {
            console.error('Error fetching unread notifications count', error);
        }
    };

    // Fetch all notifications
    const fetchAllNotifications = async () => {
        try {
            const response = await axios.get('/notifications');
            setNotifications(response.data.notifications);
        } catch (error) {
            console.error('Error fetching notifications:', error);
        }
    };

    // Function to mark a notification as read
    const markNotificationAsRead = async (notificationId) => {
        try {
            const response = await axios.patch(`/notifications/read/${notificationId}`);
            // Update unread count locally
            setUnreadCount((prev) => (prev > 0 ? prev - 1 : 0));
            return response
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    };

    useEffect(() => {
        fetchUnreadCount();
    }, []);

    return (
        <NotificationsContext.Provider
            value={{
                notifications,
                unreadCount,
                fetchUnreadCount,
                fetchAllNotifications,
                markNotificationAsRead,
            }}
        >
            {children}
        </NotificationsContext.Provider>
    );
};

// Custom hook for accessing the context
export const useNotifications = () => useContext(NotificationsContext);
