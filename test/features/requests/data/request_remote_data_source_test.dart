import 'package:campus_connect/core/constants/api_endpoints.dart';
import 'package:campus_connect/core/network/api_client.dart';
import 'package:campus_connect/features/requests/data/datasources/request_remote_data_source.dart';
import 'package:campus_connect/features/requests/data/models/request_model.dart';
import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';

class MockApiClient extends Mock implements ApiClient {}
class MockDio extends Mock implements Dio {}

void main() {
  late RequestRemoteDataSourceImpl dataSource;
  late MockApiClient mockApiClient;
  late MockDio mockDio;

  setUp(() {
    mockApiClient = MockApiClient();
    mockDio = MockDio();
    when(() => mockApiClient.dio).thenReturn(mockDio);
    dataSource = RequestRemoteDataSourceImpl(mockApiClient);
  });

  group('RequestRemoteDataSource Tests', () {
    final tRequestJson = {
      'id': 101,
      'request_type': 'Mantenimiento',
      'title': 'Fuga de agua en Laboratorio 3',
      'location': 'Pabellón B - Lab 3',
      'description': 'Hay una fuga constante en el lavamanos principal',
      'priority': 'Alta',
      'status': 'Pendiente',
      'created_at': '2026-09-22T10:00:00Z',
    };

    test('createRequest debe retornar RequestModel al enviar datos correctamente', () async {
      when(() => mockDio.post(
            ApiEndpoints.requests,
            data: any(named: 'data'),
            options: any(named: 'options'),
          )).thenAnswer(
        (_) async => Response(
          data: {'data': tRequestJson},
          statusCode: 201,
          requestOptions: RequestOptions(path: ApiEndpoints.requests),
        ),
      );

      final result = await dataSource.createRequest(
        requestType: 'Mantenimiento',
        title: 'Fuga de agua en Laboratorio 3',
        location: 'Pabellón B - Lab 3',
        description: 'Hay una fuga constante en el lavamanos principal',
        priority: 'Alta',
      );

      expect(result, isA<RequestModel>());
      expect(result.id, 101);
      expect(result.title, 'Fuga de agua en Laboratorio 3');
      expect(result.status, 'Pendiente');
    });

    test('getRequests debe retornar lista de RequestModel con filtro', () async {
      when(() => mockDio.get(
            ApiEndpoints.requests,
            queryParameters: any(named: 'queryParameters'),
          )).thenAnswer(
        (_) async => Response(
          data: {
            'data': [tRequestJson]
          },
          statusCode: 200,
          requestOptions: RequestOptions(path: ApiEndpoints.requests),
        ),
      );

      final result = await dataSource.getRequests(statusFilter: 'Pendiente');

      expect(result, isA<List<RequestModel>>());
      expect(result.length, 1);
      expect(result.first.id, 101);
    });

    test('cancelRequest debe ejecutar DELETE al endpoint correspondiente', () async {
      when(() => mockDio.delete(ApiEndpoints.requestDetail(101))).thenAnswer(
        (_) async => Response(
          data: {'message': 'Eliminada'},
          statusCode: 200,
          requestOptions: RequestOptions(path: ApiEndpoints.requestDetail(101)),
        ),
      );

      await dataSource.cancelRequest(101);

      verify(() => mockDio.delete(ApiEndpoints.requestDetail(101))).called(1);
    });
  });
}
