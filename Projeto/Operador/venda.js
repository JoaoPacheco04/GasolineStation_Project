let carrinho = [];

function adicionarProduto(id, nome, preco) {
    // Check if product already in cart
    let item = carrinho.find(p => p.id === id);
    if (item) {
        item.quantidade++;
    } else {
        carrinho.push({ id: id, nome: nome, preco: preco, quantidade: 1 });
    }
    atualizarLista();
}

function removerProduto() {
    const select = document.getElementById('listaProdutos');
    const selectedIndex = select.selectedIndex;
    if (selectedIndex === -1) return;

    // Remove selected item from carrinho
    carrinho.splice(selectedIndex, 1);
    atualizarLista();
}

function atualizarLista() {
    const select = document.getElementById('listaProdutos');
    select.innerHTML = '';

    let total = 0;

    carrinho.forEach(item => {
        const option = document.createElement('option');
        option.text = `${item.nome} — €${item.preco.toFixed(2)} x ${item.quantidade} = €${(item.preco * item.quantidade).toFixed(2)}`;
        select.add(option);
        total += item.preco * item.quantidade;
    });

    document.getElementById('total').innerText = total.toFixed(2);
    document.getElementById('total_hidden').value = total.toFixed(2);
    document.getElementById('produtos_json').value = JSON.stringify(carrinho);
}
