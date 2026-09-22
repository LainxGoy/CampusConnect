import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'app.dart';
import 'core/network/api_client.dart';
import 'core/storage/secure_storage_service.dart';
import 'features/auth/data/datasources/auth_remote_data_source.dart';
import 'features/auth/data/repositories/auth_repository_impl.dart';
import 'features/auth/domain/usecases/get_current_user_usecase.dart';
import 'features/auth/domain/usecases/login_usecase.dart';
import 'features/auth/domain/usecases/logout_usecase.dart';
import 'features/auth/presentation/providers/auth_provider.dart';
import 'features/requests/data/datasources/request_remote_data_source.dart';
import 'features/requests/data/repositories/request_repository_impl.dart';
import 'features/requests/domain/usecases/add_comment_usecase.dart';
import 'features/requests/domain/usecases/cancel_request_usecase.dart';
import 'features/requests/domain/usecases/create_request_usecase.dart';
import 'features/requests/domain/usecases/get_request_detail_usecase.dart';
import 'features/requests/domain/usecases/get_requests_usecase.dart';
import 'features/requests/domain/usecases/update_request_usecase.dart';
import 'features/requests/presentation/providers/request_provider.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // 1. Inicialización de Servicios Core
  final storageService = SecureStorageService();
  final apiClient = ApiClient(storageService: storageService);

  // 2. Módulo de Autenticación
  final authRemoteDataSource = AuthRemoteDataSourceImpl(apiClient);
  final authRepository = AuthRepositoryImpl(
    remoteDataSource: authRemoteDataSource,
    storageService: storageService,
  );
  final loginUseCase = LoginUseCase(authRepository);
  final logoutUseCase = LogoutUseCase(authRepository);
  final getCurrentUserUseCase = GetCurrentUserUseCase(authRepository);

  // 3. Módulo de Solicitudes
  final requestRemoteDataSource = RequestRemoteDataSourceImpl(apiClient);
  final requestRepository = RequestRepositoryImpl(requestRemoteDataSource);
  final createRequestUseCase = CreateRequestUseCase(requestRepository);
  final getRequestsUseCase = GetRequestsUseCase(requestRepository);
  final getRequestDetailUseCase = GetRequestDetailUseCase(requestRepository);
  final updateRequestUseCase = UpdateRequestUseCase(requestRepository);
  final cancelRequestUseCase = CancelRequestUseCase(requestRepository);
  final addCommentUseCase = AddCommentUseCase(requestRepository);

  final authProvider = AuthProvider(
    loginUseCase: loginUseCase,
    logoutUseCase: logoutUseCase,
    getCurrentUserUseCase: getCurrentUserUseCase,
  );

  // Verificar estado de sesión previo
  await authProvider.checkAuthStatus();

  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider<AuthProvider>.value(value: authProvider),
        ChangeNotifierProvider<RequestProvider>(
          create: (_) => RequestProvider(
            createRequestUseCase: createRequestUseCase,
            getRequestsUseCase: getRequestsUseCase,
            getRequestDetailUseCase: getRequestDetailUseCase,
            updateRequestUseCase: updateRequestUseCase,
            cancelRequestUseCase: cancelRequestUseCase,
            addCommentUseCase: addCommentUseCase,
          ),
        ),
      ],
      child: const CampusConnectApp(),
    ),
  );
}
