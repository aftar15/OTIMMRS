import React, { useState } from 'react';
import { View, Text, TextInput, TouchableOpacity, StyleSheet, ImageBackground, Dimensions } from 'react-native';
import { FontAwesome } from '@expo/vector-icons';
import AuthService from '../services/AuthService';

const screenHeight = Dimensions.get('window').height;
const screenWidth = Dimensions.get('window').width;
const buttonMarginBottom = screenHeight * 0.2;

export default function ForgotPasswordScreen({ navigation }) {
  const [email, setEmail] = useState('');
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');

  const handleSubmit = async () => {
    try {
      setLoading(true);
      setError('');
      setMessage('');
      
      await AuthService.forgotPassword(email);
      setMessage('Password reset instructions have been sent to your email');
      
      setTimeout(() => {
        navigation.navigate('LoginScreen');
      }, 3000);
    } catch (error) {
      setError(error.message || 'Failed to process request');
    } finally {
      setLoading(false);
    }
  };

  return (
    <ImageBackground
      source={{ uri: 'https://w0.peakpx.com/wallpaper/328/448/HD-wallpaper-palm-beach.jpg' }}
      style={styles.background}
    >
      <View style={styles.container}>
        <Text style={styles.title}>Reset Password</Text>
        
        {message && <Text style={styles.successText}>{message}</Text>}
        {error && <Text style={styles.errorText}>{error}</Text>}
        
        <TextInput
          value={email}
          onChangeText={setEmail}
          style={styles.input}
          placeholder="Enter your email"
          placeholderTextColor="#A9A9A9"
          keyboardType="email-address"
          autoCapitalize="none"
        />
        
        <TouchableOpacity 
          style={[styles.button, loading && styles.buttonDisabled]}
          onPress={handleSubmit}
          disabled={loading}
        >
          <Text style={styles.buttonText}>
            {loading ? 'Sending...' : 'Reset Password'}
          </Text>
          <FontAwesome name="angle-right" size={25} color="#fff" style={styles.icon} />
        </TouchableOpacity>
        
        <TouchableOpacity 
          onPress={() => navigation.navigate('LoginScreen')}
          style={styles.backButton}
        >
          <Text style={styles.backButtonText}>Back to Login</Text>
        </TouchableOpacity>
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
    marginTop: 20,
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    marginBottom: 30,
    color: '#fff',
  },
  input: {
    width: '80%',
    height: 50,
    borderColor: '#000',
    borderWidth: 1,
    backgroundColor: '#fff',
    borderRadius: 10,
    marginBottom: 20,
    paddingLeft: 15,
    fontSize: 16,
    color: '#000',
  },
  button: {
    marginTop: 20,
    flexDirection: 'row',
    alignItems: 'center',
    width: '50%',
    height: 50,
    backgroundColor: '#2A9DF2',
    borderRadius: 10,
    justifyContent: 'center',
    paddingStart: 30,
    paddingEnd: 20,
  },
  buttonDisabled: {
    opacity: 0.7,
  },
  buttonText: {
    fontWeight: 'bold',
    textTransform: 'uppercase',
    fontSize: 18,
    color: '#fff',
    marginRight: 20,
  },
  icon: {
    color: '#fff',
  },
  successText: {
    color: '#4CAF50',
    marginBottom: 15,
    textAlign: 'center',
    backgroundColor: 'rgba(255, 255, 255, 0.9)',
    padding: 10,
    borderRadius: 5,
    width: '80%',
  },
  errorText: {
    color: '#f44336',
    marginBottom: 15,
    textAlign: 'center',
    backgroundColor: 'rgba(255, 255, 255, 0.9)',
    padding: 10,
    borderRadius: 5,
    width: '80%',
  },
  backButton: {
    marginTop: 20,
  },
  backButtonText: {
    color: '#fff',
    fontSize: 16,
    textDecorationLine: 'underline',
  },
}); 