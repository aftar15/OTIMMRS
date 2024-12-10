import React, { useCallback, useEffect, useRef, useState } from 'react';
import { View, Text, TextInput, TouchableOpacity, ScrollView, ImageBackground, StyleSheet, Dimensions, Image, Platform } from 'react-native';
import { FontAwesome } from '@expo/vector-icons';
import { useFocusEffect, useNavigation } from '@react-navigation/native';
import * as Font from 'expo-font';
import { TouristAttractionService } from './services/TouristAttractionService';
import CookieService from './services/CookieService';
import NavigationTab from './NavigationTab';
import SearchComponent from './components/SearchComponent';
import { SearchService } from './services/SearchService';
import { getImageUrl } from './services/_api';

const { width } = Dimensions.get('window');
const imageWidth = width * 0.85;
const imageHeight = width * 0.5;
const imageWidthSeeall = width * 0.4;
const imageHeightSeeall = width * 0.3;
const marginTop = Platform.OS === 'ios' ? 50 : 30;
const marginNav = Platform.OS === 'ios' ? 30 : 20;

export default function AttractionScreen() {
  const navigation = useNavigation();
  const scrollViewRef = useRef(null);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(0);
  const [totalItems, setTotalItems] = useState(0);
  const [attractions, setAttractions] = useState([]);
  const [recommendedAttractions, setRecommendedAttractions] = useState([]);
  const [search, setSearch] = useState('');
  const [ratings, setRatings] = useState({});

  const loadAttractions = async () => {
    try {
      const currentPage = page + 1;
      setPage(currentPage);
      
      // Load popular attractions
      const response = await TouristAttractionService.getPopularAttractions({ 
        page: currentPage, 
        perPage: 10 
      }, setLoading);
      
      // Load recommended attractions
      const recommendedResponse = await TouristAttractionService.getRecommendedAttractions({ 
        page: 1, 
        perPage: 10 
      }, setLoading);
      
      setLoading(false);

      // Safely update attractions
      if (response.success && Array.isArray(response.data?.data)) {
        const newAttractions = response.data.data;
        
        // Pre-fetch ratings for new attractions
        for (const attraction of newAttractions) {
          if (attraction.id && !ratings[attraction.id]) {
            try {
              const ratingResponse = await TouristAttractionService.getRating({ id: attraction.id }, setLoading);
              if (ratingResponse.success) {
                setRatings(prev => ({
                  ...prev,
                  [attraction.id]: ratingResponse.data
                }));
              }
            } catch (error) {
              console.error('Error fetching rating:', error);
            }
          }
        }
        
        setAttractions(prevAttractions => [
          ...prevAttractions,
          ...newAttractions
        ]);
        setTotalItems(response.data.total || 0);
      }

      // Safely update recommended attractions
      if (recommendedResponse.success && Array.isArray(recommendedResponse.data?.data)) {
        setRecommendedAttractions(recommendedResponse.data.data);
      }
    } catch (error) {
      console.error('Error loading attractions:', error);
      setLoading(false);
      setAttractions([]);
      setRecommendedAttractions([]);
    }
  };

  const renderStars = (attraction) => {
    if (!attraction || !attraction.id) {
      console.log('Invalid attraction data:', attraction);
      return null;
    }

    // Use cached rating if available
    const ratingData = ratings[attraction.id] || {
      average_rating: attraction.average_rating || attraction.ratings_avg_rating || 0,
      user_rating: null
    };
    
    const rating = ratingData.average_rating;
    const displayRating = Number(rating).toFixed(1);
    const fullStars = Math.floor(rating);
    const hasHalfStar = (rating % 1) >= 0.5;

    const stars = [];
    for (let i = 0; i < 5; i++) {
      stars.push(
        <TouchableOpacity 
          key={`star-${attraction.id}-${i}`} 
          style={styles.starButton} 
          onPress={() => rate(attraction.id, i + 1)}
          disabled={loading}
        >
          <FontAwesome
            name={i < fullStars ? "star" : (i === fullStars && hasHalfStar ? "star-half-o" : "star-o")}
            size={20}
            color="#FFD700"
            style={styles.starIcon}
          />
        </TouchableOpacity>
      );
    }
    return (
      <View style={styles.ratingContainer}>
        <View style={styles.starsContainer}>{stars}</View>
        <Text style={styles.ratingText}>{displayRating}</Text>
      </View>
    );
  };

  const rate = async (id, rating) => {
    try {
      setLoading(true);
      console.log('Rating attraction:', { id, rating });
      
      const response = await TouristAttractionService.addRating({ id, rating }, setLoading);
      console.log('Rating response:', response);
      
      if (response.success || response.message?.includes('successfully')) {
        // Update the cached rating
        const ratingResponse = await TouristAttractionService.getRating({ id }, setLoading);
        if (ratingResponse.success) {
          setRatings(prev => ({
            ...prev,
            [id]: ratingResponse.data
          }));
        }
      } else {
        console.error('Failed to add rating:', response.message);
      }
    } catch (error) {
      console.error('Error rating attraction:', error);
    } finally {
      setLoading(false);
    }
  };

  const renderAttractionItem = (item, index, isRecommended = false) => {
    if (!item) return null;

    const imageStyle = isRecommended ? {
      width: imageWidthSeeall,
      height: imageHeightSeeall
    } : {
      width: imageWidth,
      height: imageHeight
    };

    return (
      <TouchableOpacity
        key={item.id || index}
        style={[styles.attractionItem, isRecommended && styles.recommendedItem]}
        onPress={() => navigation.navigate('AboutScreen', {
          id: item.id,
          title: item.name,
          location: item.location,
          imageSource: item.image_url,
          description: item.description,
          latitude: item.latitude,
          longitude: item.longitude,
          mapSource: item.map_source,
          type: 'attraction',
          rating: ratings[item.id]?.average_rating || 0
        })}
      >
        <Image
          source={{ uri: getImageUrl(item.image_url) }}
          style={imageStyle}
          onError={(e) => console.log('Image loading error:', e.nativeEvent.error)}
        />
        <View>
          <Text style={styles.scrollTitle}>{item.name}</Text>
          {renderStars(item)}
        </View>
      </TouchableOpacity>
    );
  };

  useFocusEffect(
    useCallback(() => {
      setPage(0);
      setAttractions([]);
      setRecommendedAttractions([]);
      setRatings({});
      loadAttractions();
    }, [])
  );

  const handleSearch = async (searchTerm) => {
    try {
      setSearch(searchTerm);
      if (!searchTerm.trim()) {
        setPage(0);
        setAttractions([]);
        loadAttractions();
        return;
      }

      setLoading(true);
      const response = await SearchService.searchAttractions(searchTerm);
      setLoading(false);

      if (response.success) {
        setAttractions(response.data || []);
        setTotalItems(response.data.length || 0);
      } else {
        setAttractions([]);
        setTotalItems(0);
      }
    } catch (error) {
      console.error('Search error:', error);
      setLoading(false);
      setAttractions([]);
      setTotalItems(0);
    }
  };

  const handleLoadMore = () => {
    if (!loading && attractions.length < totalItems && !search) {
      loadAttractions();
    }
  };

  return (
    <ImageBackground
      style={[styles.background, { backgroundColor: '#D0F3F5' }]} 
    >
      <View style={styles.welcomeContainer}>
        <View style={styles.row}>
          <View style={styles.column}>
            <Text style={styles.welcomeText}>Explore</Text>
            <Text style={styles.generalText}>General Luna</Text>
          </View>
          <View style={styles.column}>
            <Text style={styles.locationText}>Siargao, Philippines</Text>
          </View>
        </View>
        <View style={styles.row}>
          <SearchComponent 
            page={page} 
            setData={setAttractions} 
            setLoading={setLoading} 
            setParentSearch={setSearch} 
          />
        </View>
      </View>

      <View style={styles.containerCard}>
        {search.length == 0 && <View style={styles.rowTab}>
          <View style={styles.column}>
            <Text style={styles.tabTextActive}>Attractions</Text>
          </View>
          <View style={styles.column} >
            <Text style={styles.tabText} onPress={() => navigation.navigate('AccomodationScreen')}>Accomodations</Text>
          </View>
          <View style={styles.column}>
            <Text style={styles.tabText} onPress={() => navigation.navigate('ActivitiesScreen')}>Activities</Text>
          </View>
        </View>}
        <Text style={styles.titleText}>{search.length == 0 ? 'Popular' : 'Search Results'}</Text>
        {loading && <Text style={styles.titleText}>Loading...</Text>}
        <View style={styles.containerScroll}>
          <ScrollView ref={scrollViewRef} horizontal style={styles.row} contentContainerStyle={styles.scrollView}>
            {attractions.map((item, index) => renderAttractionItem(item, index))}
            {attractions.length < totalItems && <TouchableOpacity style={styles.columnScroll} onPress={() => loadAttractions()}>
              <View style={{ marginRight: 10 }}>
                <Text style={[styles.scrollTitle, { bottom: 0 }]}>Show More</Text>
              </View>
            </TouchableOpacity>}
          </ScrollView>
        </View>
        {search.length == 0 && <View>
          <View style={styles.rowTab}>
            <View style={styles.column}>
              <Text style={styles.titleText}>Recommended</Text>
            </View>
            <View style={styles.column}>
              <Text style={styles.tabText}></Text>
            </View>
            <View style={styles.column}>
              {recommendedAttractions.length > 0 && <Text style={styles.textSeeall}
                onPress={() => navigation.navigate('SearchScreen', {
                  type: 'attraction'
                })}
              >See all</Text>}
            </View>
          </View>
          <View style={styles.rowSeeall}>
            {recommendedAttractions.map((item, index) => renderAttractionItem(item, index, true))}
            {recommendedAttractions.length == 0 && <Text>No Recommendations</Text>}
          </View>
        </View>}
      </View>
      <NavigationTab currentActiveTab="home"/>
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
    marginTop:marginTop,
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  input: {
    flex: 1,
    borderColor: '#ccc',
    borderWidth: 0,
    padding: 7,
    marginRight: 7,
    borderRadius: 15,
    backgroundColor:'#fff',
    elevation:5,
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    marginBottom: 30,
    color: '#fff',
  },
  registrationText: {
    width :'80%',
    fontSize: 16,
    color: '#000', // Dark gray color
    textAlign: 'center',
    lineHeight: 24,
  },
  welcomeText: {
    paddingStart:25,
    width:'100%',
    padding:0,
    fontFamily: 'Kristi', // Using the font family name
    fontSize: 30, // Adjust font size as needed
  },
  generalText: {
    paddingStart:20,
    textTransform:'uppercase',
    color: '#000',
    position:'relative',
    bottom:10,
    fontSize: 20, // Adjust font size as needed
  },
  locationText:{
    textAlign:'center',
    width:'100%',
    padding:0,
    fontWeight:'bold',
    fontSize: 12, // Adjust font size as needed
  },
  recommendationText:{
    color: '#CEC93D',
    position:'relative',
    bottom:30,
    // fontFamily: 'Poppins', // Using the font family name
    fontSize: 20, // Adjust font size as needed
  },
  tabText:{
    padding:0,
    color:'#696969',
    textAlign:'center',
    fontSize: 12,
  },
  titleText:{
    width:'100%',
    fontWeight:'bold',
    marginStart:20,
    marginTop:10,
    padding:0,
    color:'#000',
    fontSize: 15,
  },
  scrollTitle:{
    alignSelf:'left',
    paddingLeft:20,
    paddingRight:20,
    paddingTop:5,
    textAlign:'center',
    paddingBottom:5,
    whiteSpace: 'wrap' ,
    width:'80%',
    left:10,
    textTransform:'uppercase',
    color: '#fff',
    position:'relative',
    bottom:90,
    borderRadius:10,
    fontSize: 12,
    backgroundColor:'#4D5652'
  },
  scrollRating:{
    alignSelf:'left',
    width:'30%',
    left:10,
    paddingLeft:10,
    paddingRight:10,
    paddingTop:5,
    paddingBottom:5,
    textTransform:'uppercase',
    color: '#fff',
    position:'relative',
    bottom:80,
    borderRadius:15,
    fontSize: 12,
    backgroundColor:'#4D5652'
  },
  tabTextActive:{
    backgroundColor: 'rgba(21, 138, 223, 0.2)', 
    borderRadius:15,
    width:'100%',
    color:'#158ADF',
    padding:0,
    textAlign:'center',
    fontSize: 12,
  },
  tabActive:{
    borderRadius:15,
    width:'100%',
    color:'#158ADF',
    padding:0,
    textAlign:'center',
    fontSize: 12,
  },
  textSeeall:{
    fontWeight:'bold',
    width:'100%',
    color:'#158ADF',
    padding:0,
    textAlign:'center',
    fontSize: 12,
    marginStart:20,
    marginTop:10,
    padding:0,
  },
  welcomeContainer:{
    padding:10,
    marginTop:marginTop,
    alignItems: 'center',
  },
  containerCard: {
    flex: 1,
    flexDirection: 'column',
  },
  containerCardSeeall:{
    borderWidth:2,
    padding:10,
    flex: 1,
    flexDirection: 'column',
  },
  containerScroll: {
    height:'55%',
    alignContent:'center',
  },
  row: {
    marginTop:5,
    flexDirection: 'row',
  },
  scrollView:{
    alignItems:'center',
  },
  rowTab: {
    marginEnd:10,
    marginStart:10,
    flexDirection: 'row',
  },
  navTab:{
    backgroundColor:"#fff",
    margin:marginNav,
    padding:marginNav,
    borderRadius:15,
    flexDirection:'row',
  },
  navColumn:{
    flex: 1,
  },
  rowSeeall:{
    marginTop:10,
    justifyContent:'center',
    flexDirection: 'row',
  },
  column: {
    flex: 1,
  },
  columnSeeall: {
    elevation:5,
    padding:5,
    borderRadius:15,
    backgroundColor:"#fff",
    margin:2,
  },
  columnScroll:{
    paddingTop:60,
    marginStart: 10,
  },
  card: {
    backgroundColor: '#f0f0f0',
    padding: 20,
    margin: 5,
  },
  cardTitle:{
    fontSize:12,
    fontWeight:'bold',
    color:'#000',
  },
  image: {
    width: imageWidth,
    height: imageHeight,
    borderRadius: 18,
    resizeMode: 'cover',
  },
  imageSeeall: {
    alignSelf: 'center',
    width: imageWidthSeeall,
    height: imageHeightSeeall,
    borderRadius: 18,
    resizeMode: 'cover',
  },
  cardTextTitleSeeall:{
    paddingStart:10,
    fontSize:12,
    fontWeight:'bold',
    color:'#000',
  },
  cardTextInfoSeeall:{
    paddingStart:10,
    fontSize:8,
    color:'#000',
  },
  ratingContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 5,
  },
  starsContainer: {
    flexDirection: 'row',
    marginRight: 5,
  },
  starButton: {
    padding: 2,
  },
  starIcon: {
    marginRight: 2,
  },
  ratingText: {
    fontSize: 14,
    color: '#666',
    marginLeft: 5,
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
  commentButton: {
    backgroundColor: '#158ADF',
    paddingHorizontal: 20,
    paddingVertical: 12,
    borderRadius: 8,
    alignItems: 'center',
    marginTop: 10,
  },
  commentButtonDisabled: {
    backgroundColor: '#ccc',
  },
  commentButtonText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 16,
  },
});
