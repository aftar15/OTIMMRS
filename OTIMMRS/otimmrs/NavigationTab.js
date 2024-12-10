import React from 'react';
import { View, Text, StyleSheet, Dimensions } from 'react-native';
import { FontAwesome } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';

const screenHeight = Dimensions.get('window').height;
const paddingnNav = screenHeight * 0.02;
const marginNav = screenHeight * 0.01;

const NavigationTab = ({ currentActiveTab }) => {
  const navigation = useNavigation();

  return (
    <View style={styles.navTab}>
      <View style={styles.navColumn}>
        <Text style={currentActiveTab === 'home' ? styles.tabActive : styles.tabText}>
          <FontAwesome name="home" size={15} color={currentActiveTab === 'home' ? '#158ADF' : '#A1A1A1'} />
        </Text>
      </View>
      {/* <View style={styles.navColumn}>
        <Text
          style={currentActiveTab === 'comment' ? styles.tabActive : styles.tabText}
          onPress={() => navigation.navigate('CommentScreen')}
        >
          <FontAwesome name="wechat" size={15} color={currentActiveTab === 'comment' ? '#158ADF' : '#A1A1A1'} />
        </Text>
      </View> */}
      <View style={styles.navColumn}>
        <Text style={currentActiveTab === 'user' ? styles.tabActive : styles.tabText}
        onPress={() => navigation.navigate('ProfileScreen')}
        >
          <FontAwesome name="user" size={15} color={currentActiveTab === 'user' ? '#158ADF' : '#A1A1A1'} />
        </Text>
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  navTab: {
    backgroundColor: '#fff',
    margin: marginNav,
    padding: paddingnNav,
    borderRadius: 15,
    flexDirection: 'row',
    elevation:5,
  },
  navColumn: {
    flex: 1,
  },
  tabActive: {
    borderRadius: 15,
    width: '100%',
    color: '#158ADF',
    padding: 0,
    textAlign: 'center',
    fontSize: 12,
  },
  tabText: {
    padding: 0,
    color: '#A1A1A1',
    textAlign: 'center',
    fontSize: 12,
  },
});

export default NavigationTab;
