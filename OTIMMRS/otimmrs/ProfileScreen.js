import React, { useEffect, useState, useCallback } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  Dimensions,
  Image,
  Alert,
  ScrollView,
  ActivityIndicator,
  RefreshControl
} from 'react-native';
import { FontAwesome } from '@expo/vector-icons';
import { useNavigation, useFocusEffect } from '@react-navigation/native';
import AuthService from './services/AuthService';
import CookieService from './services/CookieService';

const screenHeight = Dimensions.get('window').height;
const screenWidth = Dimensions.get('window').width;

export default function ProfileScreen() {
  const navigation = useNavigation();
  const [state, setState] = useState({
    isLoading: true,
    profile: null,
    error: null,
    refreshing: false
  });

  const loadProfileData = useCallback(async () => {
    try {
      const token = await CookieService.getToken();
      if (!token) {
        console.log('No token found, redirecting to login');
        navigation.reset({
          index: 0,
          routes: [{ name: 'LoginScreen' }],
        });
        return;
      }

      const response = await AuthService.getProfile();
      console.log('Profile response:', response);

      setState(prev => ({
        ...prev,
        profile: response?.data,
        error: null,
        isLoading: false,
        refreshing: false
      }));
    } catch (error) {
      console.error('Profile loading error:', error);
      setState(prev => ({
        ...prev,
        error: error.message || 'Failed to load profile',
        isLoading: false,
        refreshing: false
      }));

      if (error.response?.status === 401 || error.message.includes('token')) {
        handleLogout();
      }
    }
  }, [navigation]);

  const onRefresh = useCallback(() => {
    setState(prev => ({ ...prev, refreshing: true }));
    loadProfileData();
  }, [loadProfileData]);

  useEffect(() => {
    let mounted = true;
    
    const load = async () => {
      if (mounted) {
        await loadProfileData();
      }
    };

    load();

    return () => {
      mounted = false;
    };
  }, [loadProfileData]);

  useFocusEffect(
    useCallback(() => {
      let mounted = true;
      
      const load = async () => {
        if (mounted) {
          await loadProfileData();
        }
      };

      load();

      return () => {
        mounted = false;
      };
    }, [loadProfileData])
  );

  const handleLogout = async () => {
    try {
      await CookieService.removeToken();
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      navigation.reset({
        index: 0,
        routes: [{ name: 'LoginScreen' }],
      });
    }
  };

  const confirmLogout = () => {
    Alert.alert(
      'Log out',
      'Are you sure you want to log out?',
      [
        { text: 'Cancel', style: 'cancel' },
        { text: 'YES', onPress: handleLogout },
      ],
      { cancelable: true }
    );
  };

  if (state.isLoading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#0000ff" />
      </View>
    );
  }

  if (state.error) {
    return (
      <View style={styles.errorContainer}>
        <Text style={styles.errorText}>{state.error}</Text>
        <TouchableOpacity onPress={loadProfileData} style={styles.retryButton}>
          <Text style={styles.retryButtonText}>Retry</Text>
        </TouchableOpacity>
      </View>
    );
  }

  if (!state.profile) {
    return (
      <View style={styles.errorContainer}>
        <Text style={styles.errorText}>No profile data available</Text>
        <TouchableOpacity onPress={handleLogout} style={styles.logoutButton}>
          <Text style={styles.logoutButtonText}>Back to Login</Text>
        </TouchableOpacity>
      </View>
    );
  }

  return (
    <ScrollView 
      style={styles.container}
      refreshControl={
        <RefreshControl
          refreshing={state.refreshing}
          onRefresh={onRefresh}
        />
      }
    >
      <View style={styles.header}>
        <Image
          source={state.profile.avatar 
            ? { uri: state.profile.avatar } 
            : require('./assets/default-avatar.png')}
          style={styles.avatar}
        />
        <Text style={styles.name}>{state.profile.full_name || 'N/A'}</Text>
        <Text style={styles.email}>{state.profile.email || 'N/A'}</Text>
      </View>

      <View style={styles.infoContainer}>
        <InfoItem icon="venus-mars" label="Gender" value={state.profile.gender || 'N/A'} />
        <InfoItem icon="flag" label="Nationality" value={state.profile.nationality || 'N/A'} />
        <InfoItem icon="map-marker" label="Address" value={state.profile.address || 'N/A'} />
        <InfoItem icon="hotel" label="Accommodation" value={state.profile.accommodation_name || 'N/A'} />
        <InfoItem icon="map" label="Location" value={state.profile.accommodation_location || 'N/A'} />
        <InfoItem icon="calendar" label="Duration" value={`${state.profile.accommodation_days || 0} days`} />
      </View>

      <TouchableOpacity onPress={confirmLogout} style={styles.logoutButton}>
        <FontAwesome name="sign-out" size={20} color="#fff" />
        <Text style={styles.logoutButtonText}>Log Out</Text>
      </TouchableOpacity>
    </ScrollView>
  );
}

const InfoItem = ({ icon, label, value }) => (
  <View style={styles.infoItem}>
    <FontAwesome name={icon} size={20} color="#666" style={styles.infoIcon} />
    <View style={styles.infoText}>
      <Text style={styles.infoLabel}>{label}</Text>
      <Text style={styles.infoValue}>{value}</Text>
    </View>
  </View>
);

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F5F5F5',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#F5F5F5',
  },
  errorContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
    backgroundColor: '#F5F5F5',
  },
  errorText: {
    fontSize: 16,
    color: '#FF0000',
    textAlign: 'center',
    marginBottom: 20,
  },
  retryButton: {
    backgroundColor: '#4A90E2',
    padding: 10,
    borderRadius: 5,
  },
  retryButtonText: {
    color: '#FFF',
    fontSize: 16,
  },
  header: {
    alignItems: 'center',
    padding: 20,
    backgroundColor: '#FFF',
    borderBottomWidth: 1,
    borderBottomColor: '#E0E0E0',
  },
  avatar: {
    width: 100,
    height: 100,
    borderRadius: 50,
    marginBottom: 10,
  },
  name: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#333',
  },
  email: {
    fontSize: 16,
    color: '#666',
    marginTop: 5,
  },
  infoContainer: {
    backgroundColor: '#FFF',
    margin: 10,
    borderRadius: 10,
    padding: 15,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  infoItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: '#E0E0E0',
  },
  infoIcon: {
    marginRight: 15,
    width: 25,
  },
  infoText: {
    flex: 1,
  },
  infoLabel: {
    fontSize: 14,
    color: '#666',
  },
  infoValue: {
    fontSize: 16,
    color: '#333',
    marginTop: 2,
  },
  logoutButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#FF3B30',
    margin: 20,
    padding: 15,
    borderRadius: 10,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  logoutButtonText: {
    color: '#FFF',
    fontSize: 18,
    fontWeight: 'bold',
    marginLeft: 10,
  },
});
