import * as Font from 'expo-font';
import { FontAwesome } from '@expo/vector-icons';

export const loadFonts = async () => {
  try {
    await Font.loadAsync({
      'Kristi': require('../assets/fonts/Kristi-Regular.ttf'),
      'Poppins': require('../assets/fonts/Poppins-Bold.ttf'),
      ...FontAwesome.font,
    });
    return true;
  } catch (error) {
    console.error('Error loading fonts:', error);
    return false;
  }
}; 