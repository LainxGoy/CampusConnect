import 'package:campus_connect/core/network/auth_interceptor.dart';
import 'package:campus_connect/core/storage/secure_storage_service.dart';
import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';

class MockSecureStorageService extends Mock implements SecureStorageService {}
class MockRequestInterceptorHandler extends Mock implements RequestInterceptorHandler {}
class MockErrorInterceptorHandler extends Mock implements ErrorInterceptorHandler {}

void main() {
  late AuthInterceptor interceptor;
  late MockSecureStorageService mockStorage;
  late MockRequestInterceptorHandler mockHandler;

  setUp(() {
    mockStorage = MockSecureStorageService();
    mockHandler = MockRequestInterceptorHandler();
    interceptor = AuthInterceptor(mockStorage);
  });

  test('debe inyectar Bearer token en headers si existe en SecureStorage', () async {
    when(() => mockStorage.getToken()).thenAnswer((_) async => 'fake-jwt-token-123');
    final options = RequestOptions(path: '/test');

    await interceptor.onRequest(options, mockHandler);

    expect(options.headers['Authorization'], 'Bearer fake-jwt-token-123');
    verify(() => mockHandler.next(options)).called(1);
  });

  test('no debe inyectar Authorization si el token es nulo', () async {
    when(() => mockStorage.getToken()).thenAnswer((_) async => null);
    final options = RequestOptions(path: '/test');

    await interceptor.onRequest(options, mockHandler);

    expect(options.headers.containsKey('Authorization'), false);
    verify(() => mockHandler.next(options)).called(1);
  });
}
