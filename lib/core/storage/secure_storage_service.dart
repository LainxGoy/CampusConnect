import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../errors/exceptions.dart';

class SecureStorageService {
  final FlutterSecureStorage _storage;

  static const String _keyToken = 'auth_token';
  static const String _keyUser = 'auth_user_data';

  SecureStorageService({FlutterSecureStorage? storage})
      : _storage = storage ??
            const FlutterSecureStorage(
              aOptions: AndroidOptions(encryptedSharedPreferences: true),
              iOptions: IOSOptions(accessibility: KeychainAccessibility.first_unlock),
            );

  Future<void> saveToken(String token) async {
    try {
      await _storage.write(key: _keyToken, value: token);
    } catch (e) {
      throw CacheException('No se pudo guardar el token de autenticación');
    }
  }

  Future<String?> getToken() async {
    try {
      return await _storage.read(key: _keyToken);
    } catch (e) {
      return null;
    }
  }

  Future<void> saveUserData(String jsonString) async {
    try {
      await _storage.write(key: _keyUser, value: jsonString);
    } catch (e) {
      throw CacheException('No se pudo guardar la información del usuario');
    }
  }

  Future<String?> getUserData() async {
    try {
      return await _storage.read(key: _keyUser);
    } catch (e) {
      return null;
    }
  }

  Future<void> deleteToken() async {
    try {
      await _storage.delete(key: _keyToken);
      await _storage.delete(key: _keyUser);
    } catch (e) {
      throw CacheException('Error al eliminar credenciales guardadas');
    }
  }

  Future<bool> hasToken() async {
    final token = await getToken();
    return token != null && token.isNotEmpty;
  }
}
