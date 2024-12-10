import React, { useState, useEffect } from 'react';
import { View, Text, TouchableOpacity, StyleSheet, ImageBackground, ActivityIndicator } from 'react-native';
import { FontAwesome } from '@expo/vector-icons';
import AuthService from '../services/AuthService';

export default function EmailVerificationScreen({ route, navigation }) {
  const [verifying, setVerifying] = useState(true);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState(false);

  // Get the verification token from navigation params
  const { token } = route.params || {};

  useEffect(() => {
    verifyEmail();
  }, []);

  const verifyEmail = async () => {
    try {
      if (!token) {
        setError('Invalid verification link');
        setVerifying(false);
        return;
      }

      await AuthService.verifyEmail(token);
      setSuccess(true);
      
      // Redirect to login after successful verification
      setTimeout(() => {
        navigation.navigate('LoginScreen');
      }, 3000);

    } catch (error) {
      setError(error.message || 'Email verification failed');
    } finally {
      setVerifying(false);
    }
  };

  return (
    <ImageBackground
      source={{ uri: 'https://w0.peakpx.com/wallpaper/328/448/HD-wallpaper-palm-beach.jpg' }}
      style={styles.background}
    >
      <View style={styles.container}>
        <Text style={styles.title}>Email Verification</Text>

        {verifying ? (
          <View style={styles.loadingContainer}>
            <ActivityIndicator size="large" color="#fff" />
            <Text style={styles.loadingText}>Verifying your email...</Text>
          </View>
        ) : success ? (
          <View style={styles.messageContainer}>
            <FontAwesome name="check-circle" size={50} color="#4CAF50" />
            <Text style={styles.successText}>Email verified successfully!</Text>
            <Text style={styles.redirectText}>Redirecting to login...</Text>
          </View>
        ) : (
          <View style={styles.messageContainer}>
            <FontAwesome name="times-circle" size={50} color="#f44336" />
            <Text style={styles.errorText}>{error}</Text>
            <TouchableOpacity 
              style={styles.button}
              onPress={() => navigation.navigate('LoginScreen')}
            >
              <Text style={styles.buttonText}>Back to Login</Text>
            </TouchableOpacity>
          </View>
        )}
      </View>
    </ImageBackground>
  );
}

const styles = StyleSheet.create({
  background: {
    flex: 1,
    resizeMode: "cover",
  },
  container: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: 20,
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    marginBottom: 30,
    color: '#fff',
  },
  loadingContainer: {
    alignItems: 'center',
  },
  loadingText: {
    color: '#fff',
    marginTop: 15,
    fontSize: 16,
  },
  messageContainer: {
    alignItems: 'center',
    backgroundColor: 'rgba(255, 255, 255, 0.9)',
    padding: 20,
    borderRadius: 10,
    width: '100%',
  },
  successText: {
    color: '#4CAF50',
    fontSize: 18,
    fontWeight: 'bold',
    marginTop: 15,
    textAlign: 'center',
  },
  errorText: {
    color: '#f44336',
    fontSize: 16,
    marginTop: 15,
    textAlign: 'center',
  },
  redirectText: {
    color: '#666',
    marginTop: 10,
    fontSize: 14,
  },
  button: {
    backgroundColor: '#2196F3',
    padding: 15,
    borderRadius: 25,
    width: '100%',
    marginTop: 20,
  },
  buttonText: {
    color: '#fff',
    textAlign: 'center',
    fontSize: 16,
    fontWeight: 'bold',
  },
}); 