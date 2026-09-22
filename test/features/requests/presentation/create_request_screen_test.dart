import 'package:campus_connect/features/requests/presentation/providers/request_provider.dart';
import 'package:campus_connect/features/requests/presentation/screens/create_request_screen.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';
import 'package:provider/provider.dart';

class MockRequestProvider extends Mock implements RequestProvider {}

void main() {
  late MockRequestProvider mockProvider;

  setUp(() {
    mockProvider = MockRequestProvider();
    when(() => mockProvider.isLoading).thenReturn(false);
  });

  Widget buildTestableWidget() {
    return MaterialApp(
      home: ChangeNotifierProvider<RequestProvider>.value(
        value: mockProvider,
        child: const CreateRequestScreen(),
      ),
    );
  }

  testWidgets('Dispara validaciones de formulario si los campos obligatorios están vacíos',
      (WidgetTester tester) async {
    // Definimos un viewport suficientemente amplio o aseguramos visibilidad
    tester.view.physicalSize = const Size(1080, 2400);
    tester.view.devicePixelRatio = 1.0;
    addTearDown(() {
      tester.view.resetPhysicalSize();
      tester.view.resetDevicePixelRatio();
    });

    await tester.pumpWidget(buildTestableWidget());
    await tester.pumpAndSettle();

    final submitButton = find.byKey(const Key('btn_submit_request'));
    expect(submitButton, findsOneWidget);

    await tester.ensureVisible(submitButton);
    await tester.tap(submitButton);
    await tester.pumpAndSettle();

    expect(find.text('Seleccione el tipo de solicitud'), findsOneWidget);
    expect(find.text('El campo Título es obligatorio'), findsOneWidget);
    expect(find.text('El campo Ubicación es obligatorio'), findsOneWidget);
    expect(find.text('El campo Descripción es obligatorio'), findsOneWidget);

    verifyNever(() => mockProvider.createRequest(
          requestType: any(named: 'requestType'),
          title: any(named: 'title'),
          location: any(named: 'location'),
          description: any(named: 'description'),
          priority: any(named: 'priority'),
        ));
  });
}
