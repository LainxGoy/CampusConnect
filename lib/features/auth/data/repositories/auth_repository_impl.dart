import '../../../../core/storage/secure_storage_service.dart';
import '../../domain/entities/user_entity.dart';
import '../../domain/repositories/auth_repository.dart';
import '../datasources/auth_remote_data_source.dart';
import '../models/user_model.dart';

class AuthRepositoryImpl implements AuthRepository {
  final AuthRemoteDataSource _remoteDataSource;
  final SecureStorageService _storageService;

  AuthRepositoryImpl({
    required AuthRemoteDataSource remoteDataSource,
    required SecureStorageService storageService,
  })  : _remoteDataSource = remoteDataSource,
        _storageService = storageService;

  @override
  Future<UserEntity> login(String email, String password) async {
    final result = await _remoteDataSource.login(email, password);
    if (result.token.isNotEmpty) {
      await _storageService.saveToken(result.token);
    }
    await _storageService.saveUserData(result.user.toRawJson());
    return result.user;
  }

  @override
  Future<void> logout() async {
    try {
      await _remoteDataSource.logout();
    } catch (_) {
      // Incluso si falla la red en logout, borramos localmente
    } finally {
      await _storageService.deleteToken();
    }
  }

  @override
  Future<UserEntity?> getCurrentUser() async {
    final rawUser = await _storageService.getUserData();
    if (rawUser != null && rawUser.isNotEmpty) {
      try {
        return UserModel.fromRawJson(rawUser);
      } catch (_) {
        return null;
      }
    }
    return null;
  }

  @override
  Future<bool> isAuthenticated() async {
    return await _storageService.hasToken();
  }
}
