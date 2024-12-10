import { StatusBar, StyleSheet } from 'react-native';
import { NavigationContainer } from '@react-navigation/native';
import { createStackNavigator } from '@react-navigation/stack';
import { NativeBaseProvider } from 'native-base';
import React, { useState, useEffect } from 'react';
import * as SplashScreen from 'expo-splash-screen';
import { loadFonts } from './utils/fonts';
import HomeScreen from './HomeScreen';
import LoginScreen from './LoginScreen';
import RegistrationScreen from './RegistrationScreen';
import SearchScreen from './SearchScreen';
import AttractionScreen from './AttractionScreen';
import AccomodationScreen from './AccomodationScreen';
import ActivitiesScreen from './ActivitiesScreen';
import CommentScreen from './CommentScreen';
import ProfileScreen from './screens/ProfileScreen';
import AboutScreen from './AboutScreen';
import MapScreen from './MapScreen';
import Recommended from './Recommended';
import { AuthProvider } from './contexts/AuthContext';
import ForgotPasswordScreen from './screens/ForgotPasswordScreen';
import ResetPasswordScreen from './screens/ResetPasswordScreen';
import EmailVerificationScreen from './screens/EmailVerificationScreen';

const Stack = createStackNavigator();

// Prevent the splash screen from auto-hiding
SplashScreen.preventAutoHideAsync();

export default function App() {
  const [appIsReady, setAppIsReady] = useState(false);

  useEffect(() => {
    async function prepare() {
      try {
        // Load fonts
        await loadFonts();
      } catch (e) {
        console.warn(e);
      } finally {
        setAppIsReady(true);
      }
    }

    prepare();
  }, []);

  useEffect(() => {
    if (appIsReady) {
      // Hide splash screen once fonts are loaded
      SplashScreen.hideAsync();
    }
  }, [appIsReady]);

  if (!appIsReady) {
    return null;
  }

  return (
    <AuthProvider>
      <NavigationContainer>
        <Stack.Navigator initialRouteName="Home">
          <Stack.Screen name="Home" component={HomeScreen} options={{ headerShown: false }} />
          <Stack.Screen name="LoginScreen" component={LoginScreen} options={{ headerShown: false }} />
          <Stack.Screen name="RegistrationScreen" component={RegistrationScreen} options={{ headerShown: false }} />
          <Stack.Screen name="AccomodationScreen" component={AccomodationScreen} options={{ headerShown: false }} />
          <Stack.Screen name="SearchScreen" component={SearchScreen} options={{ headerShown: false }} />
          <Stack.Screen name="AttractionScreen" component={AttractionScreen} options={{ headerShown: false }} />
          <Stack.Screen name="ActivitiesScreen" component={ActivitiesScreen} options={{ headerShown: false }} />
          <Stack.Screen name="CommentScreen" component={CommentScreen} options={{ headerShown: false }} />
          <Stack.Screen name="ProfileScreen" component={ProfileScreen} options={{ headerShown: false }} />
          <Stack.Screen name="AboutScreen" component={AboutScreen} options={{ headerShown: false }} />
          <Stack.Screen name="MapScreen" component={MapScreen} options={{ headerShown: false }} />
          <Stack.Screen name="Recommended" component={Recommended} options={{ headerShown: false }} />
          <Stack.Screen 
            name="ForgotPassword" 
            component={ForgotPasswordScreen} 
            options={{ headerShown: false }} 
          />
          <Stack.Screen 
            name="ResetPassword" 
            component={ResetPasswordScreen} 
            options={{ headerShown: false }} 
          />
          <Stack.Screen 
            name="EmailVerification" 
            component={EmailVerificationScreen} 
            options={{ headerShown: false }} 
          />
        </Stack.Navigator>
      </NavigationContainer>
    </AuthProvider>
  );
}
