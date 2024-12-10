import React, { useEffect, useState } from 'react';
import { TextInput, StyleSheet} from 'react-native';
import { useNavigation } from '@react-navigation/native';
import CookieService from '../services/CookieService';
import { SearchService } from '../services/SearchService';

export default function SearchComponent({
  page,
  setData,
  setLoading,
  setParentSearch
}) {
  const navigation = useNavigation();
  const [search, setSearch] = useState('');
  const [debouncedSearch, setDebouncedSearch] = useState('');

  const executeSearch = async () => {
    try {
      setLoading(true);
      const response = await SearchService.search({ search, page }, setLoading);
      setData(response.data.data);
      setLoading(false);
    } catch (error) {
      console.log('ERROR :', error);
      if (error.logout) {
        await CookieService.removeToken();
        navigation.navigate('LoginScreen');
      }
    }
  }

  useEffect(() => {
    const timer = setTimeout(() => {
      setDebouncedSearch(search);
      setParentSearch(search);
    }, 1500); // Debounce delay of 1.5 seconds

    // Cleanup the timeout if search changes before the timeout is triggered
    return () => clearTimeout(timer);
  }, [search]);

  useEffect(() => {
    if (debouncedSearch && debouncedSearch.length > 0) {
      executeSearch();
    }
  }, [debouncedSearch]);

  return (
    <TextInput
      value={search}
      onChangeText={setSearch}
      style={styles.input}
      placeholder="Find things to do"
    />
  );
}

const styles = StyleSheet.create({
  input: {
    flex: 1,
    borderColor: '#ccc',
    borderWidth: 0,
    padding: 7,
    marginRight: 7,
    borderRadius: 15,
    backgroundColor:'#fff',
    elevation:5,
  }
});
