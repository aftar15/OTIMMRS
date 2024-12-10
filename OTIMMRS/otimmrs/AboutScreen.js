import React, { useEffect, useState } from 'react';
import { View, Text, ImageBackground, StyleSheet, Dimensions, Image, Alert, ScrollView, TextInput, TouchableOpacity } from 'react-native';
import { FontAwesome } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import * as Font from 'expo-font';
import { CookieService } from './services/CookieService';
import { TouristAttractionService } from './services/TouristAttractionService';
import { ActivityService } from './services/ActivityService';
import { AccommodationService } from './services/AccommodationService';
import { getImageUrl } from './services/_api';

const screenHeight = Dimensions.get('window').height;
const screenWidth = Dimensions.get('window').width;
const marginTop = screenHeight * 0.01;
const padding = screenWidth * 0.02;
const marginEnd = screenHeight * 0.02;
const imageWidth = screenHeight * 0.45;
const imageHeight = screenHeight * 0.43;
const heartIcon = screenHeight * 0.03;
const showmap = screenHeight * 0.48;
const marginBottom = screenHeight * 0.1;
const heartButtonSize = screenWidth * 0.15;

const AboutScreen = ({ route }) => {
  const { id, title, location, latitude, longitude, mapSource, imageSource, description, type } = route.params;
        
  const navigation = useNavigation();
  const [loading, setLoading] = useState(false);
  const [rating, setRating] = useState({ average_rating: 0 });
  const [userRating, setUserRating] = useState(null);
  const [commentData, setCommentData] = useState({
    transportation: '',
    transportation_fee: '',
    services: '',
    road_problems: '',
    price_increase: '',
    others: ''
  });
  const [commentErrors, setCommentErrors] = useState({});
  const [showCommentForm, setShowCommentForm] = useState(false);

  const loadRatings = async () => {
    try {
      let service;
      console.log('Loading ratings for type:', type);
      
      // Validate type
      if (!['activity', 'accommodation', 'attraction'].includes(type)) {
        console.error('Invalid type:', type);
        return;
      }

      // Select the appropriate service
      if (type === 'activity') {
        service = ActivityService;
      } else if (type === 'accommodation') {
        service = AccommodationService;
      } else if (type === 'attraction') {
        service = TouristAttractionService;
      }

      console.log('Using service for ratings:', { type, serviceExists: !!service });
      const response = await service.getRating({ id }, setLoading);
      console.log('Rating response:', response);
      
      if (response && response.data) {
        const avgRating = response.data.average_rating || 0;
        const usrRating = response.data.user_rating || null;
        console.log('Setting ratings:', { avgRating, usrRating });
        
        setRating(prevRating => ({
          ...prevRating,
          average_rating: avgRating
        }));
        
        if (usrRating) {
          setUserRating(usrRating);
        }
      }
    } catch (error) {
      console.error('Error fetching rating:', error);
      if (error.logout) {
        await CookieService.removeToken();
        navigation.navigate('LoginScreen');
      }
    }
  };

  useEffect(() => {
    const loadFonts = async () => {
      try {
        await Font.loadAsync({
          'Kristi': require('./assets/fonts/Kristi-Regular.ttf'),
          'Poppins': require('./assets/fonts/Poppins-Bold.ttf'),
        });
      } catch (error) {
        console.log('Error loading fonts:', error);
      }
    };

    loadFonts();
    loadRatings();
  }, []);

  const rate = async (rateValue) => {
    if (userRating) {
      Alert.alert('Already Rated', 'You have already rated this destination.');
      return;
    }

    console.log('Starting rating process', { id, rateValue, type });

    // Validate type
    if (!['activity', 'accommodation', 'attraction'].includes(type)) {
      Alert.alert('Error', 'Invalid destination type');
      return;
    }

    Alert.alert(
      'Confirm Rating',
      `Rate ${rateValue} star${rateValue > 1 ? 's' : ''} for this destination?`,
      [
        {
          text: 'Cancel',
          style: 'cancel'
        },
        {
          text: 'Confirm',
          onPress: async () => {
            try {
              console.log('Rating confirmed, sending request');
              setLoading(true);
              let service;
              
              // Select the appropriate service
              if (type === 'activity') {
                service = ActivityService;
                console.log('Using ActivityService');
              } else if (type === 'accommodation') {
                service = AccommodationService;
                console.log('Using AccommodationService');
              } else if (type === 'attraction') {
                service = TouristAttractionService;
                console.log('Using TouristAttractionService');
              }

              if (!service) {
                throw new Error('Invalid service type');
              }

              console.log('Sending rating request', { id, rating: rateValue, type });
              const response = await service.addRating({ id, rating: rateValue }, setLoading);
              console.log('Rating response received', response);
              
              if (response && (response.success || response.status === 'success')) {
                setUserRating(rateValue);
                await loadRatings(); // Reload ratings to get the new average
                Alert.alert('Success', 'Your rating has been added successfully');
              } else {
                throw new Error(response?.message || 'Failed to add rating');
              }
            } catch (error) {
              console.error('Error adding rating:', error);
              Alert.alert('Error', error.message || 'Failed to add rating');
              if (error.logout) {
                await CookieService.removeToken();
                navigation.navigate('LoginScreen');
              }
            } finally {
              setLoading(false);
            }
          }
        }
      ]
    );
  };

  const navigateToComment = () => {
    console.log('Navigating to comment screen with:', { id, type });
    
    // Ensure we're using the correct type for the backend
    let modelType = type;
    if (type === 'attraction') {
      modelType = 'tourist_attraction';
    }
    
    navigation.navigate('CommentScreen', { 
      id, 
      type: modelType,
      title,
      returnScreen: 'AboutScreen'
    });
  };

  const handleComment = async () => {
    try {
      // Reset errors
      setCommentErrors({});
      
      // Validate required fields
      const requiredFields = ['transportation', 'transportation_fee', 'services', 'road_problems', 'price_increase'];
      const errors = {};
      
      requiredFields.forEach(field => {
        if (!commentData[field] || !commentData[field].toString().trim()) {
          errors[field] = `${field.replace(/_/g, ' ')} is required`;
        }
      });

      if (Object.keys(errors).length > 0) {
        setCommentErrors(errors);
        alert('Please fill in all required fields');
        return;
      }

      if (!id) {
        alert('Invalid attraction ID');
        return;
      }

      setLoading(true);
      console.log('Submitting comment:', { id, ...commentData });
      
      const response = await TouristAttractionService.addComment({ 
        id,
        ...commentData
      }, setLoading);
      
      console.log('Comment response:', response);
      
      if (response.success) {
        alert('Comment added successfully');
        // Clear form and hide it
        setCommentData({
          transportation: '',
          transportation_fee: '',
          services: '',
          road_problems: '',
          price_increase: '',
          others: ''
        });
        setShowCommentForm(false);
      } else {
        alert(response.message || 'Failed to add comment. Please try again.');
      }
    } catch (error) {
      console.error('Error submitting comment:', error);
      alert('Error adding comment. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  const renderStars = () => {
    const stars = [];
    const displayRating = Number(userRating || rating?.average_rating || 0);
    const fullStars = Math.floor(displayRating);
    const hasHalfStar = (displayRating % 1) >= 0.5;
    const totalStars = 5;

    console.log('Rendering stars with:', { 
      userRating, 
      averageRating: rating?.average_rating,
      displayRating,
      fullStars,
      hasHalfStar 
    });

    for (let i = 0; i < totalStars; i++) {
      const isFilled = i < fullStars;
      const isHalf = !isFilled && i === fullStars && hasHalfStar;
      const starName = isFilled ? "star" : (isHalf ? "star-half-o" : "star-o");
      
      stars.push(
        <TouchableOpacity 
          key={`star-${i}`} 
          style={styles.starButton} 
          onPress={() => rate(i + 1)}
          disabled={loading || userRating !== null}
        >
          <FontAwesome
            name={starName}
            size={25}
            color="#FFD700"
            style={styles.starIcon}
          />
        </TouchableOpacity>
      );
    }

    const ratingDisplay = Number(displayRating).toFixed(1);

    return (
      <View style={styles.ratingContainer}>
        <View style={styles.stars}>
          {stars}
        </View>
        <Text style={styles.ratingText}>
          {displayRating > 0 ? `${ratingDisplay} / 5.0` : 'No ratings yet'}
        </Text>
        {userRating && (
          <Text style={styles.userRatingText}>
            Your rating: {Number(userRating).toFixed(1)}
          </Text>
        )}
      </View>
    );
  };

  const renderCommentForm = () => {
    if (!showCommentForm) return null;

    const handleInputChange = (field, value) => {
      setCommentData(prev => ({
        ...prev,
        [field]: value
      }));
      // Clear error when user starts typing
      if (commentErrors[field]) {
        setCommentErrors(prev => ({
          ...prev,
          [field]: null
        }));
      }
    };
    
    return (
      <View style={styles.commentContainer}>
        <Text style={styles.commentTitle}>Add Your Experience</Text>
        
        <TextInput
          style={[styles.commentInput, commentErrors.transportation && styles.inputError]}
          placeholder="Transportation Type (e.g., Bus, Car) *"
          value={commentData.transportation}
          onChangeText={(text) => handleInputChange('transportation', text)}
          maxLength={100}
        />
        {commentErrors.transportation && (
          <Text style={styles.errorText}>{commentErrors.transportation}</Text>
        )}
        
        <TextInput
          style={[styles.commentInput, commentErrors.transportation_fee && styles.inputError]}
          placeholder="Transportation Fee (in PHP) *"
          value={commentData.transportation_fee}
          onChangeText={(text) => handleInputChange('transportation_fee', text.replace(/[^0-9]/g, ''))}
          keyboardType="numeric"
          maxLength={10}
        />
        {commentErrors.transportation_fee && (
          <Text style={styles.errorText}>{commentErrors.transportation_fee}</Text>
        )}
        
        <TextInput
          style={[styles.commentInput, commentErrors.services && styles.inputError]}
          placeholder="Available Services *"
          value={commentData.services}
          onChangeText={(text) => handleInputChange('services', text)}
          multiline
          maxLength={500}
        />
        {commentErrors.services && (
          <Text style={styles.errorText}>{commentErrors.services}</Text>
        )}
        
        <TextInput
          style={[styles.commentInput, commentErrors.road_problems && styles.inputError]}
          placeholder="Road Conditions/Problems *"
          value={commentData.road_problems}
          onChangeText={(text) => handleInputChange('road_problems', text)}
          multiline
          maxLength={500}
        />
        {commentErrors.road_problems && (
          <Text style={styles.errorText}>{commentErrors.road_problems}</Text>
        )}
        
        <TextInput
          style={[styles.commentInput, commentErrors.price_increase && styles.inputError]}
          placeholder="Price Changes/Increases *"
          value={commentData.price_increase}
          onChangeText={(text) => handleInputChange('price_increase', text)}
          multiline
          maxLength={500}
        />
        {commentErrors.price_increase && (
          <Text style={styles.errorText}>{commentErrors.price_increase}</Text>
        )}
        
        <TextInput
          style={[styles.commentInput, styles.commentInputMultiline]}
          placeholder="Additional Comments (optional)"
          value={commentData.others}
          onChangeText={(text) => handleInputChange('others', text)}
          multiline
          numberOfLines={3}
          maxLength={500}
        />
        
        <View style={styles.buttonContainer}>
          <TouchableOpacity 
            style={[
              styles.commentButton,
              (loading) && styles.commentButtonDisabled
            ]}
            onPress={handleComment}
            disabled={loading}
          >
            <Text style={styles.commentButtonText}>
              {loading ? 'Submitting...' : 'Submit Experience'}
            </Text>
          </TouchableOpacity>
          
          <TouchableOpacity 
            style={[styles.cancelButton]}
            onPress={() => setShowCommentForm(false)}
          >
            <Text style={styles.cancelButtonText}>Cancel</Text>
          </TouchableOpacity>
        </View>
      </View>
    );
  };

  return (
    <ScrollView style={styles.container}>
      <ImageBackground style={[styles.background, { backgroundColor: '#D0F3F5' }]}>
        <View style={styles.contentContainer}>
          <Image
            source={{ uri: getImageUrl(imageSource) }}
            style={styles.image}
            resizeMode="cover"
          />
          <View style={styles.buttonContainer}>
            <TouchableOpacity
              style={styles.button}
              onPress={() => navigation.navigate('MapScreen', {
                latitude: latitude,
                longitude: longitude,
                title: title,
                mapSource: mapSource
              })}
            >
              <Text style={styles.buttonText}>Show Map</Text>
            </TouchableOpacity>

            <TouchableOpacity
              style={styles.button}
              onPress={() => navigation.navigate('CommentScreen', {
                id: route.params.id,
                type: route.params.type || 'tourist_attraction',  
                title: route.params.title,
                returnScreen: 'AboutScreen'
              })}
            >
              <Text style={styles.buttonText}>Add Comment</Text>
            </TouchableOpacity>
          </View>

          <View style={styles.detailsContainer}>
            <Text style={styles.title}>{title}</Text>
            <Text style={styles.location}>{location}</Text>
            <View style={styles.ratingContainer}>
              {renderStars()}
              <Text style={styles.ratingText}>
                {Number(userRating || rating?.average_rating || 0).toFixed(1)} / 5.0
              </Text>
              {userRating && (
                <Text style={styles.userRatingText}>Your rating: {userRating}</Text>
              )}
            </View>
            <Text style={styles.description}>{description}</Text>
          </View>
        </View>
      </ImageBackground>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  background: {
    flex: 1,
    resizeMode: "cover",
  },
  contentContainer: {
    padding: 20,
    paddingTop: 40,
  },
  image: {
    width: '100%',
    height: imageHeight,
    borderRadius: 15,
  },
  buttonContainer: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    marginTop: 10,
  },
  button: {
    backgroundColor: '#2A9DF2',
    paddingVertical: 10,
    paddingHorizontal: 20,
    borderRadius: 8,
  },
  buttonText: {
    color: '#fff',
    fontWeight: 'bold',
  },
  detailsContainer: {
    padding: 10,
  },
  ratingContainer: {
    alignItems: 'center',
    marginBottom: 15,
  },
  stars: {
    flexDirection: 'row',
    marginBottom: 5,
  },
  starButton: {
    padding: 5,
  },
  starIcon: {
    marginRight: 2,
  },
  ratingText: {
    fontSize: 16,
    color: '#666',
    marginTop: 5,
  },
  userRatingText: {
    fontSize: 14,
    color: '#2A9DF2',
    marginTop: 5,
    fontWeight: 'bold',
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    marginBottom: 5,
    color: '#000',
  },
  location: {
    fontSize: 16,
    color: '#666',
    marginBottom: 15,
  },
  description: {
    fontSize: 16,
    color: '#333',
    lineHeight: 24,
  },
  commentContainer: {
    padding: 15,
    backgroundColor: '#fff',
    borderRadius: 10,
    marginHorizontal: 15,
    marginVertical: 10,
    elevation: 2,
  },
  commentTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 15,
    color: '#333',
  },
  commentInput: {
    borderColor: '#ddd',
    borderWidth: 1,
    borderRadius: 8,
    paddingHorizontal: 15,
    paddingVertical: 10,
    marginBottom: 5,
    backgroundColor: '#fff',
    fontSize: 16,
  },
  inputError: {
    borderColor: '#ff0000',
  },
  errorText: {
    color: '#ff0000',
    fontSize: 12,
    marginBottom: 10,
    marginLeft: 5,
  },
  commentInputMultiline: {
    minHeight: 80,
    textAlignVertical: 'top',
  },
  buttonContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginTop: 10,
  },
  commentButton: {
    flex: 1,
    backgroundColor: '#158ADF',
    paddingHorizontal: 20,
    paddingVertical: 12,
    borderRadius: 8,
    alignItems: 'center',
    marginRight: 10,
  },
  commentButtonDisabled: {
    backgroundColor: '#ccc',
  },
  commentButtonText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 16,
  },
  cancelButton: {
    flex: 1,
    backgroundColor: '#f5f5f5',
    paddingHorizontal: 20,
    paddingVertical: 12,
    borderRadius: 8,
    alignItems: 'center',
    marginLeft: 10,
  },
  cancelButtonText: {
    color: '#333',
    fontWeight: 'bold',
    fontSize: 16,
  },
});

export default AboutScreen;
