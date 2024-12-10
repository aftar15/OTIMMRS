import React, { useEffect, useState } from 'react';
import { ScrollView, View, Text, TextInput, TouchableOpacity, ImageBackground, StyleSheet, Dimensions, Modal, ActivityIndicator } from 'react-native';
import { FontAwesome } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import AuthService from './services/AuthService';
import CookieService from './services/CookieService';

const screenHeight = Dimensions.get('window').height;
const margin = screenHeight * 0.1;
const imageWidth = screenHeight * 0.45;
const imageHeight = screenHeight * 0.45;

const HOBBY_OPTIONS = [
  { id: 'beach', label: 'Beach & Water Activities', categories: ['beach', 'water_sports'] },
  { id: 'nature', label: 'Nature & Hiking', categories: ['hiking', 'nature', 'camping'] },
  { id: 'culture', label: 'Cultural & Historical', categories: ['museums', 'temples', 'historical'] },
  { id: 'adventure', label: 'Adventure Sports', categories: ['surfing', 'diving', 'climbing'] },
  { id: 'food', label: 'Food & Culinary', categories: ['restaurants', 'food_tours', 'cooking'] },
  { id: 'relaxation', label: 'Relaxation & Wellness', categories: ['spa', 'yoga', 'meditation'] },
  { id: 'nightlife', label: 'Nightlife & Entertainment', categories: ['bars', 'clubs', 'shows'] },
  { id: 'shopping', label: 'Shopping & Markets', categories: ['markets', 'malls', 'souvenirs'] },
];

const GENDER_OPTIONS = ['Male', 'Female', 'Other'];

export default function RegistrationScreen() {
  const navigation = useNavigation();
  const [loadingPage, setLoadingPage] = useState(true);
  const [loading, setLoading] = useState(false);
  const [fullName, setFullName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [gender, setGender] = useState('');
  const [address, setAddress] = useState('');
  const [nationality, setNationality] = useState('');
  const [selectedHobbies, setSelectedHobbies] = useState([]);
  const [accommodationName, setAccommodationName] = useState('');
  const [accommodationLocation, setAccommodationLocation] = useState('');
  const [accommodationDays, setAccommodationDays] = useState('');
  const [showSuccessModal, setShowSuccessModal] = useState(false);
  const [showGenderModal, setShowGenderModal] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');

  useEffect(() => {
    const checkCredential = async() => {
      const token = await CookieService.getToken();
      if (token) {
        navigation.navigate('AttractionScreen');
      }
      setLoadingPage(false);
    }

    checkCredential();
  }, []);

  const toggleHobby = (hobbyId) => {
    setSelectedHobbies(prev => {
      if (prev.includes(hobbyId)) {
        return prev.filter(id => id !== hobbyId);
      } else {
        return [...prev, hobbyId];
      }
    });
  };

  const handleRegister = async () => {
    setErrorMessage('');
    
    if (!fullName || !email || !password || !address || !nationality || 
        !gender || !selectedHobbies.length || !accommodationName || 
        !accommodationLocation || !accommodationDays) {
      setErrorMessage('Please fill in all fields');
      return;
    }

    try {
      setLoading(true);
      const hobbiesData = selectedHobbies.map(id => {
        const hobby = HOBBY_OPTIONS.find(h => h.id === id);
        return {
          name: hobby.label,
          categories: hobby.categories
        };
      });

      const userData = {
        fullName,
        email,
        password,
        gender,
        nationality,
        address,
        hobbies: JSON.stringify(hobbiesData),
        accommodationName,
        accommodationLocation,
        accommodationDays
      };

      await AuthService.register(userData);
      
      setShowSuccessModal(true);
      setTimeout(() => {
        setShowSuccessModal(false);
        navigation.navigate('LoginScreen');
      }, 2000);
    } catch (error) {
      console.error('Registration error:', error);
      setErrorMessage(error.message || 'Registration failed. Please try again.');
    } finally {
      setLoading(false);
    }

  };

  if (loadingPage) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#0000ff" />
      </View>
    );
  }

  return (
    <ImageBackground
      source={{ uri: 'https://w0.peakpx.com/wallpaper/328/448/HD-wallpaper-palm-beach.jpg' }}
      style={styles.background}
    >
      <ScrollView style={styles.container}>
        <View style={styles.formContainer}>
          <Text style={styles.title}>Registration Form</Text>
          
          {errorMessage ? <Text style={styles.errorText}>{errorMessage}</Text> : null}

          <TextInput
            value={fullName}
            onChangeText={setFullName}
            style={styles.input}
            placeholder="Full Name"
            placeholderTextColor="#A9A9A9"
          />
          <TextInput
            value={email}
            onChangeText={setEmail}
            style={styles.input}
            placeholder="Email"
            placeholderTextColor="#A9A9A9"
            keyboardType="email-address"
          />
          <TextInput
            value={password}
            onChangeText={setPassword}
            style={styles.input}
            placeholder="Password"
            placeholderTextColor="#A9A9A9"
            secureTextEntry
          />
          <TouchableOpacity
            style={styles.input}
            onPress={() => setShowGenderModal(true)}
          >
            <Text style={[styles.inputText, !gender && styles.placeholder]}>
              {gender || "Select Gender"}
            </Text>
          </TouchableOpacity>

          <Modal
            visible={showGenderModal}
            transparent={true}
            animationType="slide"
            onRequestClose={() => setShowGenderModal(false)}
          >
            <View style={styles.modalContainer}>
              <View style={styles.modalContent}>
                {GENDER_OPTIONS.map((option) => (
                  <TouchableOpacity
                    key={option}
                    style={styles.modalOption}
                    onPress={() => {
                      setGender(option);
                      setShowGenderModal(false);
                    }}
                  >
                    <Text style={styles.modalOptionText}>{option}</Text>
                  </TouchableOpacity>
                ))}
                <TouchableOpacity
                  style={[styles.modalOption, styles.cancelButton]}
                  onPress={() => setShowGenderModal(false)}
                >
                  <Text style={[styles.modalOptionText, styles.cancelText]}>Cancel</Text>
                </TouchableOpacity>
              </View>
            </View>
          </Modal>

          <TextInput
            value={nationality}
            onChangeText={setNationality}
            style={styles.input}
            placeholder="Nationality"
            placeholderTextColor="#A9A9A9"
          />
          <TextInput
            value={address}
            onChangeText={setAddress}
            style={styles.input}
            placeholder="Address"
            placeholderTextColor="#A9A9A9"
          />

          <Text style={styles.sectionTitle}>Select Your Interests</Text>
          <Text style={styles.sectionSubtitle}>Choose activities you enjoy (select multiple)</Text>
          <View style={styles.hobbiesContainer}>
            {HOBBY_OPTIONS.map((hobby) => (
              <TouchableOpacity
                key={hobby.id}
                style={[
                  styles.hobbyButton,
                  selectedHobbies.includes(hobby.id) && styles.hobbyButtonSelected
                ]}
                onPress={() => toggleHobby(hobby.id)}
              >
                <Text style={[
                  styles.hobbyButtonText,
                  selectedHobbies.includes(hobby.id) && styles.hobbyButtonTextSelected
                ]}>
                  {hobby.label}
                </Text>
              </TouchableOpacity>
            ))}
          </View>

          <TextInput
            value={accommodationName}
            onChangeText={setAccommodationName}
            style={styles.input}
            placeholder="Accommodation Name"
            placeholderTextColor="#A9A9A9"
          />
          <TextInput
            value={accommodationLocation}
            onChangeText={setAccommodationLocation}
            style={styles.input}
            placeholder="Accommodation Location"
            placeholderTextColor="#A9A9A9"
          />
          <TextInput
            value={accommodationDays}
            onChangeText={setAccommodationDays}
            style={styles.input}
            placeholder="Number of Days"
            placeholderTextColor="#A9A9A9"
            keyboardType="numeric"
          />

          <TouchableOpacity 
            style={styles.button} 
            onPress={handleRegister}
            disabled={loading}
          >
            <Text style={styles.buttonText}>
              {loading ? 'Creating Account...' : 'Create Account'}
            </Text>
            <FontAwesome name="angle-right" size={25} color="#fff" style={styles.icon} />
          </TouchableOpacity>

          <TouchableOpacity 
            style={styles.loginLink}
            onPress={() => navigation.navigate('LoginScreen')}
          >
            <Text style={styles.loginText}>Already have an account? Login</Text>
          </TouchableOpacity>
        </View>
      </ScrollView>

      <Modal
        animationType="fade"
        transparent={true}
        visible={showSuccessModal}
      >
        <View style={styles.modalContainer}>
          <View style={styles.modalContent}>
            <FontAwesome name="check-circle" size={50} color="#4CAF50" />
            <Text style={styles.modalText}>Registration Successful!</Text>
          </View>
        </View>
      </Modal>
    </ImageBackground>
  );
}

const styles = StyleSheet.create({
  background: {
    flex: 1,
    resizeMode: "cover",
    justifyContent: "center",
  },
  container: {
    flex: 1,
  },
  formContainer: {
    padding: 20,
    paddingTop: 40,
  },
  title: {
    fontSize: 28,
    fontWeight: 'bold',
    marginBottom: 30,
    color: '#000',
    textAlign: 'center',
  },
  input: {
    backgroundColor: '#fff',
    padding: 15,
    borderRadius: 10,
    marginBottom: 15,
    fontSize: 16,
  },
  inputText: {
    fontSize: 16,
    color: '#000',
  },
  sectionTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#000',
    marginTop: 20,
    marginBottom: 5,
  },
  sectionSubtitle: {
    fontSize: 14,
    color: '#666',
    marginBottom: 15,
  },
  hobbiesContainer: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
    marginBottom: 20,
  },
  hobbyButton: {
    width: '48%',
    backgroundColor: '#fff',
    padding: 15,
    borderRadius: 10,
    marginBottom: 10,
    alignItems: 'center',
  },
  hobbyButtonSelected: {
    backgroundColor: '#158ADF',
  },
  hobbyButtonText: {
    color: '#666',
    fontSize: 14,
  },
  hobbyButtonTextSelected: {
    color: '#fff',
    fontWeight: 'bold',
  },
  button: {
    backgroundColor: '#158ADF',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    padding: 15,
    borderRadius: 10,
    marginTop: 20,
  },
  buttonText: {
    color: '#fff',
    fontSize: 18,
    fontWeight: 'bold',
    marginRight: 10,
  },
  icon: {
    marginLeft: 10,
  },
  errorText: {
    color: '#ff0000',
    marginBottom: 15,
    textAlign: 'center',
  },
  loginLink: {
    marginTop: 20,
    alignItems: 'center',
  },
  loginText: {
    color: '#158ADF',
    fontSize: 16,
  },
  modalContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
  },
  modalContent: {
    backgroundColor: 'white',
    borderRadius: 10,
    padding: 20,
    width: '80%',
  },
  modalOption: {
    paddingVertical: 15,
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  modalOptionText: {
    fontSize: 16,
    textAlign: 'center',
  },
  cancelButton: {
    marginTop: 10,
    borderBottomWidth: 0,
  },
  cancelText: {
    color: 'red',
  },
  placeholder: {
    color: '#999',
  },
  modalText: {
    fontSize: 18,
    marginTop: 15,
    color: '#000',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
});
