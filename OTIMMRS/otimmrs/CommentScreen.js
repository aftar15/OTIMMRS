import React, { useState } from 'react';
import { View, Text, TextInput, TouchableOpacity, ImageBackground, StyleSheet, Dimensions, Alert } from 'react-native';
import { FontAwesome } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import { TouristAttractionService } from './services/TouristAttractionService';
import { ActivityService } from './services/ActivityService';
import { AccommodationService } from './services/AccommodationService';
import CookieService from './services/CookieService';

const screenHeight = Dimensions.get('window').height;
const margin = screenHeight * 0.1;

export default function CommentScreen({ route }) {
  const navigation = useNavigation();
  const { id, type, title, returnScreen } = route.params;

  const [transportation, setTransportation] = useState('');
  const [transportationFee, setTransportationFee] = useState('');
  const [services, setServices] = useState('');
  const [roadProblems, setRoadProblems] = useState('');
  const [priceIncrease, setPriceIncrease] = useState('');
  const [others, setOthers] = useState('');
  const [loading, setLoading] = useState(false);

  const validateFields = () => {
    const fields = [
      { value: transportation, name: 'Transportation' },
      { value: transportationFee, name: 'Transportation Fee' },
      { value: roadProblems, name: 'Road Problems' },
      { value: priceIncrease, name: 'Price Increase' }
    ];

    for (const field of fields) {
      if (!field.value?.trim()) {
        Alert.alert('Error', `Please enter ${field.name}`);
        return false;
      }
    }
    return true;
  };

  const getService = () => {
    switch (type) {
      case 'tourist_attraction':
      case 'attraction':
        return TouristAttractionService;
      case 'activity':
        return ActivityService;
      case 'accommodation':
        return AccommodationService;
      default:
        throw new Error(`Unsupported type: ${type}`);
    }
  };

  const addComment = async () => {
    if (!validateFields()) {
      return;
    }

    if (!id) {
      Alert.alert('Error', 'Attraction ID is required');
      return;
    }

    try {
      setLoading(true);
      console.log('Adding comment for:', { id, type });
      
      const service = getService();
      
      // Prepare the payload with the ID included
      const payload = {
        id,  // Include the ID in the base payload
        transportation: transportation.trim(),
        transportation_fee: parseFloat(transportationFee.trim()) || 0,
        services: services.trim(),
        road_problems: roadProblems.trim(),
        price_increase: priceIncrease.trim(),
        others: others.trim() || ''
      };

      console.log('Sending comment with payload:', payload);
      const response = await service.addComment(payload, setLoading);
      console.log('Comment response:', response);

      if (response && (response.success || response.status === 'success')) {
        Alert.alert('Success', response.message || 'Comment added successfully');
        navigation.goBack();
      } else if (response && response.errors) {
        const errorMessage = Object.values(response.errors).flat().join('\n');
        Alert.alert('Error', errorMessage);
      } else {
        throw new Error(response?.message || 'Failed to add comment');
      }
    } catch (error) {
      console.error('ERROR:', error);
      Alert.alert('Error', error.message || 'Failed to add comment');
    } finally {
      setLoading(false);
    }
  };

  return (
    <ImageBackground
      style={[styles.background, { backgroundColor: '#D0F3F5' }]}
    >
      <View style={styles.container}>
        <Text style={styles.title}>Add Comment</Text>
        {title && <Text style={styles.subtitle}>{title}</Text>}

        <TextInput
          value={transportation}
          onChangeText={setTransportation}
          style={styles.input}
          placeholder="Transportation *"
          placeholderTextColor="#A9A9A9"
        />

        <TextInput
          value={transportationFee}
          onChangeText={setTransportationFee}
          style={styles.input}
          placeholder="Transportation Fee"
          placeholderTextColor="#A9A9A9"
          keyboardType="numeric"
        />

        <TextInput
          value={services}
          onChangeText={setServices}
          style={styles.input}
          placeholder="Services"
          placeholderTextColor="#A9A9A9"
        />

        <TextInput
          value={roadProblems}
          onChangeText={setRoadProblems}
          style={styles.input}
          placeholder="Road Problems"
          placeholderTextColor="#A9A9A9"
        />

        <TextInput
          value={priceIncrease}
          onChangeText={setPriceIncrease}
          style={styles.input}
          placeholder="Price Increase"
          placeholderTextColor="#A9A9A9"
        />

        <TextInput
          value={others}
          onChangeText={setOthers}
          style={[styles.input, styles.commentInput]}
          placeholder="Additional Comments"
          placeholderTextColor="#A9A9A9"
          multiline
          numberOfLines={4}
          textAlignVertical="top"
        />

        <View style={styles.buttonContainer}>
          <TouchableOpacity 
            style={[styles.button, loading && styles.buttonDisabled]} 
            onPress={addComment}
            disabled={loading}
          >
            <Text style={styles.buttonText}>
              {loading ? 'Submitting...' : 'Submit'}
            </Text>
            {!loading && <FontAwesome name="angle-right" size={25} color="#fff" style={styles.icon} />}
          </TouchableOpacity>
        </View>
      </View>
    </ImageBackground>
  );
}

const styles = StyleSheet.create({
  background: {
    flex: 1,
    resizeMode: "cover",
    justifyContent: "center"
  },
  container: {
    flex: 1,
    justifyContent: "center",
    alignItems: 'center',
    padding: 20
  },
  title: {
    fontSize: 40,
    fontWeight: 'bold',
    marginBottom: 30,
    color: '#B6B136',
  },
  input: {
    width: '80%',
    height: 50,
    borderColor: '#000',
    borderWidth: 1,
    backgroundColor: '#fff',
    borderRadius: 10,
    marginBottom: 15,
    paddingHorizontal: 15,
    fontSize: 16,
    color: '#000',
  },
  commentInput: {
    height: 100,
    paddingTop: 10,
    marginBottom: 20,
  },
  buttonContainer: {
    width: '100%',
    alignItems: 'center',
    marginTop: 10,
  },
  button: {
    flexDirection: 'row',
    alignItems: 'center',
    width: '50%',
    backgroundColor: '#2A9DF2',
    borderRadius: 7,
    justifyContent: 'center',
    padding: 12,
  },
  buttonDisabled: {
    backgroundColor: '#A0A0A0',
  },
  buttonText: {
    fontWeight: 'bold',
    textTransform: 'uppercase',
    fontSize: 18,
    color: '#fff',
    marginRight: 10,
  },
  icon: {
    color: '#fff',
  },
  subtitle: {
    fontSize: 18,
    color: '#666',
    marginBottom: 20,
    textAlign: 'center',
  }
});
