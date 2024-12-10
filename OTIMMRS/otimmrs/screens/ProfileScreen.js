import React, { useEffect, useState } from 'react';
import { View, Text, TouchableOpacity, StyleSheet, ImageBackground, Image, Alert, Dimensions, Platform } from 'react-native';
import { FontAwesome } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import { useAuth } from '../contexts/AuthContext';
import { ProfileService } from '../services/ProfileService';
import CookieService from '../services/CookieService';
import AuthService from '../services/AuthService';
import axios from 'axios';

const screenHeight = Dimensions.get('window').height;
const screenWidth = Dimensions.get('window').width;
const marginStart = screenWidth * 0.1;
const marginTop = screenHeight * 0.05;

export default function ProfileScreen() {
  const navigation = useNavigation();
  const auth = useAuth();
  const [loading, setLoading] = useState(true);
  const [loggingOut, setLoggingOut] = useState(false);
  const [profile, setProfile] = useState({});

  useEffect(() => {
    loadProfile();
  }, []);

  const loadProfile = async () => {
    try {
      const token = await CookieService.getToken();
      if (!token) {
        navigation.reset({
          index: 0,
          routes: [{ name: 'LoginScreen' }],
        });
        return;
      }

      const response = await ProfileService.getProfile();
      setProfile(response.data);
    } catch (error) {
      console.error('Profile loading error:', error);
      if (axios.isAxiosError(error) && !error.response) {
        Alert.alert('Network Error', 'Please check your internet connection');
      } else {
        handleLogout();
      }
    } finally {
      setLoading(false);
    }
  };

  const confirmLogout = () => {
    if (Platform.OS === 'web') {
      handleLogout();
    } else {
      Alert.alert(
        'Log out',
        'Are you sure you want to log out?',
        [
          {
            text: 'Cancel',
            style: 'cancel',
          },
          { 
            text: 'YES', 
            onPress: handleLogout 
          },
        ]
      );
    }
  };

  const handleLogout = async () => {
    try {
      setLoggingOut(true);
      
      // Remove token first to prevent further API calls
      await CookieService.removeToken();
      
      // Try to call logout API, but don't wait for it
      try {
        await AuthService.logout();
      } catch (error) {
        console.error('API logout failed:', error);
        // Continue with local logout even if API call fails
      }
      
      // Call auth context logout
      if (auth && auth.logout) {
        await auth.logout();
      }
      
      // Reset local state
      setProfile({});
      
      // Navigate to login screen
      navigation.reset({
        index: 0,
        routes: [{ name: 'LoginScreen' }],
      });
    } catch (error) {
      console.error('Logout error:', error);
      if (axios.isAxiosError(error) && !error.response) {
        Alert.alert('Network Error', 'Please check your internet connection');
      } else {
        Alert.alert('Error', 'Failed to logout. Please try again.');
      }
    } finally {
      setLoggingOut(false);
    }
  };

  return (
    <ImageBackground
      style={[styles.background, { backgroundColor: '#D0F3F5' }]}
    >
      <View style={styles.container}>
        <Text style={styles.title}
          onPress={() => navigation.navigate('AttractionScreen')}
        >Home</Text>

        <Image
          source={{ uri: 'https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_960_720.png' }}
          style={styles.profileImage}
        />
        <View style={{ marginStart: marginStart, marginTop: marginTop }}>
          <Text style={styles.text}>Name: {profile.full_name}</Text>
          <Text style={styles.text}>Email: {profile.email}</Text>
          <Text style={styles.text}>Address: {profile.address}</Text>
          <Text style={[styles.text, { textTransform: 'capitalize' }]}>Gender: {profile.gender}</Text>
          <Text style={styles.text}>Nationality: {profile.nationality}</Text>
          <Text style={styles.text}>Hobbies: {profile.hobbies}</Text>
        </View>

        <View style={styles.submit}>
          <TouchableOpacity 
            style={styles.button} 
            onPress={confirmLogout}
            disabled={loggingOut}
          >
            <Text style={styles.buttonText}>
              {loggingOut ? 'Logging Out...' : 'Log out'}
            </Text>
            <FontAwesome name="angle-right" size={25} color="#fff" style={styles.icon} />
          </TouchableOpacity>
        </View>
      </View>
    </ImageBackground>
  );
}

const styles = StyleSheet.create({
  background: {
    flex: 1,
  },
  container: {
    flex: 1,
    padding: 20,
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#000',
    marginBottom: 20,
  },
  profileImage: {
    width: 120,
    height: 120,
    borderRadius: 60,
    alignSelf: 'center',
  },
  text: {
    fontSize: 16,
    marginBottom: 10,
    color: '#000',
  },
  submit: {
    marginTop: 30,
  },
  button: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#2A9DF2',
    padding: 15,
    borderRadius: 10,
    justifyContent: 'center',
  },
  buttonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
    marginRight: 10,
  },
  icon: {
    color: '#fff',
  },
}); 