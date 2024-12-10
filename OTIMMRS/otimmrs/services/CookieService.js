import AsyncStorage from '@react-native-async-storage/async-storage';

const TOKEN_KEY = 'authToken';

class CookieService {
  static async setToken(token) {
    if (!token) {
      throw new Error('Token cannot be null or undefined');
    }
    try {
      // Ensure token is a string
      const tokenString = typeof token === 'object' ? JSON.stringify(token) : String(token);
      await AsyncStorage.setItem(TOKEN_KEY, tokenString);
      console.log('Token saved successfully');
    } catch (error) {
      console.error('Failed to save token:', error);
      throw error;
    }
  }

  static async getToken() {
    try {
      const token = await AsyncStorage.getItem(TOKEN_KEY);
      if (!token) {
        console.log('No token found');
        return null;
      }
      // Try to parse the token in case it was stored as JSON
      try {
        return JSON.parse(token);
      } catch {
        return token; // Return as is if it's not JSON
      }
    } catch (error) {
      console.error('Failed to get token:', error);
      return null;
    }
  }

  static async removeToken() {
    try {
      await AsyncStorage.removeItem(TOKEN_KEY);
      console.log('Token removed successfully');
    } catch (error) {
      console.error('Failed to remove token:', error);
      throw error;
    }
  }
}

export default CookieService;
