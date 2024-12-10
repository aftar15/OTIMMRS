import React, { useCallback, useEffect, useRef, useState } from 'react';
import { View, Text, TextInput, TouchableOpacity, ScrollView, ImageBackground, StyleSheet, Dimensions, Image, Alert } from 'react-native';
import { FontAwesome } from '@expo/vector-icons';
import { useFocusEffect, useNavigation } from '@react-navigation/native';
import * as Font from 'expo-font';
import NavigationTab from './NavigationTab';
import { ActivityService } from './services/ActivityService';
import SearchComponent from './components/SearchComponent';
import { TouristAttractionService } from './services/TouristAttractionService';

const screenHeight = Dimensions.get('window').height;
const marginTop = screenHeight * 0.02;
const marginNav = screenHeight * 0.02;
const imageWidth = screenHeight * 0.3;
const imageHeight = screenHeight * 0.35;
const imageWidthSeeall = screenHeight * 0.2;
const imageHeightSeeall = screenHeight * 0.12;

const ActivityImage = ({ uri, style }) => {
  const [hasError, setHasError] = useState(false);

  return (
    <View style={style}>
      <Image
        source={hasError ? require('./assets/icon.png') : { uri }}
        style={[style, { resizeMode: 'cover' }]}
        onError={() => setHasError(true)}
      />
    </View>
  );
};

export default function ActivitiesScreen() {
  const navigation = useNavigation();
  const scrollViewRef = useRef(null);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(0);
  const [activities, setActivities] = useState([]);
  const [totalItems, setTotalItems] = useState(0);
  const [recommendedActivities, setRecommendedActivities] = useState([]);
  const [search, setSearch] = useState('');

  const loadActivities = async () => {
    try {
      setLoading(true)
      const currentPage = page + 1
      setPage(currentPage)
      
      const response = await ActivityService.getActivities({ 
        page: currentPage, 
        perPage: 2 
      })
      
      if (response?.success) {
        setActivities([
          ...activities,
          ...(response.data?.data || [])
        ])
        setTotalItems(response.data?.total || 0)
      } else {
        Alert.alert(
          'Error', 
          response?.message || 'Failed to load activities',
          [{ text: 'OK' }]
        )
      }
    } catch (error) {
      console.error('Failed to load activities:', error)
      Alert.alert(
        'Error',
        'Failed to load activities. Please try again later.',
        [{ text: 'OK' }]
      )
    } finally {
      setLoading(false)
    }
  }
  
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
  }, []);
  
  useFocusEffect(
    useCallback(() => {
      loadActivities();
      if (scrollViewRef.current) {
        scrollViewRef.current.scrollTo({ x: 0, animated: true });
      }
    }, [])
  );

  return (
    <ImageBackground
        style={[styles.background, { backgroundColor: '#D0F3F5' }]} 
        >
            {/* TRY */}
            {/* ---------------------- */}

     <View style={styles.welcomeContainer}>
      {/* SECTION 1 */}
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
        setData={setActivities} 
        setLoading={setLoading} 
        setParentSearch={setSearch} 
      />
      </View>

      



      {/* END OF ROWS */}
     
      </View>
      {/* END SECTION 1*/}
      {/* END SECTION 2*/}
{/* ---------------------- */}

      <View style={styles.containerCard}>
      {/* ROWSS */}
        {search.length == 0 && <View style={styles.rowTab}>
        <View style={styles.column}>
          <Text style={styles.tabText} onPress={() => navigation.navigate('AttractionScreen')}>Attractions</Text>
        </View>
        <View style={styles.column} >
          <Text style={styles.tabText} onPress={() => navigation.navigate('AccomodationScreen')} >Accomodations</Text>
        </View>
        <View style={styles.column}>
          <Text style={styles.tabTextActive}>Activities</Text>
        </View>
      </View>}
{/* ---------------------- */}

      <Text style={styles.titleText}>{search.length == 0 ? 'Popular' : 'Search Results'}</Text>
      {loading && <Text style={styles.titleText}>Loading...</Text>}
{/* ---------------------- */}

      {/* ROWSS */}
      <View style={styles.containerScroll}>
        <ScrollView ref={scrollViewRef} horizontal style={styles.row} contentContainerStyle={styles.scrollView}>
          {activities.map(activity => <TouchableOpacity key={`${activity.name}-${activity.id}`} style={styles.columnScroll}
            onPress={() => 
              navigation.navigate('AboutScreen', {
                id: activity.id,
                title: activity.name,
                location: activity.location,
                imageSource: activity.image_url,
                description: activity.description,
                latitude: activity.latitude,
                longitude: activity.longitude,
                mapSource: activity.map_source,
                type: 'activity'
             })}
            >
              <ActivityImage
                uri={activity.image_url}
                style={styles.image}
              />
              <View>
              <Text style={styles.scrollTitle}>{activity.name}</Text>
              {activity.ratings_avg_rating && <Text style={styles.scrollRating}>
              <FontAwesome
                    name="star"
                    size={13}
                    color="#FFD700"/>&nbsp;
                {Number(activity.ratings_avg_rating).toFixed(1)}
              </Text>}
              </View>
            </TouchableOpacity>)}
            {activities.length < totalItems && <TouchableOpacity style={styles.columnScroll} onPress={() => loadActivities()}>
              <View style={{ marginRight: 10 }}>
                <Text style={[styles.scrollTitle, { bottom: 0 }]}>Show More</Text>
              </View>
            </TouchableOpacity>}
          </ScrollView>
      </View>
{/* ---------------------- */}
          <View >
          {/* ROWSS */}
            {search.length == 0 && <View style={styles.rowTab}>
              <View style={styles.column}>
                <Text style={styles.titleText}>Recommended</Text>
              </View>
              <View style={styles.column}>
                <Text style={styles.tabText}></Text>
              </View>
              <View style={styles.column}>
              {recommendedActivities.length > 0 && <Text style={styles.textSeeall}
              onPress={() => navigation.navigate('SearchScreen', {
                type: 'activity'
              })}
              >See all</Text>}
              </View>
            </View>}
            {/* ROWSS */}
          </View>
{/* ---------------------- */}

          {/* LAST CONTAINER */}
          {/* ROWSS */}
            {search.length == 0 && <View style={styles.rowSeeall}>
              {recommendedActivities.map(activity => 
              <TouchableOpacity key={`${activity.name}-${activity.id}`} style={styles.columnSeeall}
                onPress={() => 
                  navigation.navigate('AboutScreen', {
                    id: activity.id,
                    title: activity.name,
                    location: activity.location,
                    imageSource: activity.image_url,
                    description: activity.description,
                    latitude: activity.latitude,
                    longitude: activity.longitude,
                    mapSource: activity.map_source,
                    type: 'activity'
                })}>
                  <ActivityImage
                    uri={activity.image_url}
                    style={styles.imageSeeall}
                  />
                <Text style={styles.cardTextTitleSeeall}>{activity.name}</Text>
                <Text style={styles.cardTextInfoSeeall}>Hot Deal</Text>
              </TouchableOpacity>)}
              {recommendedActivities.length == 0 && <Text>No Recommendations</Text>}
            </View>}
            {/* ROWSS */}
          </View>
          {/* END LAST CONTAINER */}

          <NavigationTab currentActiveTab="home"/>

          {/* TRY */}
      


      
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
    paddingStart:25,
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
    backgroundColor: 'rgba(21, 138, 223, 0.2)', // Adjust the alpha value as needed
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
  },
  imageSeeall:{
    alignSelf:'center',
    width: imageWidthSeeall,
    height: imageHeightSeeall,
    borderRadius: 18,
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
});
